<?php

function set_value(string|array $key, mixed $value = ''):bool
{
	global $USER_DATA;

	$called_from = debug_backtrace();
	$ikey = array_search(__FUNCTION__, array_column($called_from, 'function'));
	$path = get_plugin_dir(debug_backtrace()[$ikey]['file']) . 'config.json';

	if(file_exists($path))
	{
		$json = json_decode(file_get_contents($path));
		$plugin_id = $json->id;

		if(is_array($key))
		{
			foreach ($key as $k => $value) {
				
				$USER_DATA[$plugin_id][$k] = $value;
			}
		}else
		{
			$USER_DATA[$plugin_id][$key] = $value;
		}


		return true;
	}

	return false;
}

function plugin_id():string
{
	$called_from = debug_backtrace();
	$ikey = array_search(__FUNCTION__, array_column($called_from, 'function'));
	$path = get_plugin_dir(debug_backtrace()[$ikey]['file']) . 'config.json';

	$json = json_decode(file_get_contents($path));
	return $json->id ?? '';
}

function get_value(string $key = ''):mixed
{
	global $USER_DATA;

	$called_from = debug_backtrace();
	$ikey = array_search(__FUNCTION__, array_column($called_from, 'function'));
	$path = get_plugin_dir(debug_backtrace()[$ikey]['file']) . 'config.json';

	if(file_exists($path))
	{
		$json = json_decode(file_get_contents($path));
		$plugin_id = $json->id;

		if(empty($key))
			return $USER_DATA[$plugin_id];

		return !empty($USER_DATA[$plugin_id][$key]) ? $USER_DATA[$plugin_id][$key] : null;
	}

	return null;

}

function APP($key = '')
{
	global $APP;

	if(!empty($key))
	{
		return !empty($APP[$key]) ? $APP[$key] : null;
	}else{

		return $APP;
	}

	return null;
}

function dnd($data)
{
	echo "<pre><div style='margin:1px;background-color:#000;color:white;padding:5px 10px'>";
	print_r($data);
	echo "</div></pre>";
}

function redirect($url)
{
	header("Location: ". ROOT .'/'. $url);
	die;
}

/**
 * Spilt Url.
 */
function split_url($url)
{
	return explode("/", trim($url,'/'));
}

/**
 * Url Method.
 *
 * @param string $key
 * @return void
 */
function URL($key = '')
{
	global $APP;

	if(is_numeric($key) || !empty($key))
	{
		if(!empty($APP['URL'][$key]))
		{
			return $APP['URL'][$key];
		}
	}else{
		return $APP['URL'];
	}

	return '';
}

/**
 * Page Method.
 *
 * @return void
 */
function page()
{
	return URL(0);
}

/**
 * Get all available plugins.
 *
 * @return void
 */
function get_plugin_folders()
{
    $plugins_folder = 'plugins/';
    $res = [];
    $folders = scandir($plugins_folder);
    foreach ($folders as $folder) {
        if ($folder != '.' && $folder != '..' && is_dir($plugins_folder . $folder))
            $res[] = $folder;
    }

    return $res;
}

/**
 * Load Plugins
 *
 * @param [type] $plugin_folders
 * @return void
 */
function load_plugins($plugin_folders)
{
	global $APP;
	$loaded = false;
	$dependencies = [];

	foreach ($plugin_folders as $folder) {
		
		$file = 'plugins/' . $folder . '/config.json';
		if(file_exists($file))
		{
			$json = json_decode(file_get_contents($file));
			
			if(is_object($json) && isset($json->id))
			{
				if(!empty($json->active))
				{
					$file = 'plugins/' . $folder . '/plugin.php';
					if(file_exists($file) && valid_route($json))
					{
						$json->index = $json->index ?? 1;
						$json->version = $json->version ?? "1.0.0";
						$json->dependencies = $json->dependencies ?? (object)[];
						$json->index_file = $file;
						$json->path = 'plugins/' . $folder . '/';
						$json->http_path = ROOT . '/' . $json->path;

						$APP['plugins'][] = $json;

					}
				}
			}
		}
	}

	if(!empty($APP['plugins']))
	{
		$APP['plugins'] = sort_plugins($APP['plugins']);
		foreach ($APP['plugins'] as $json)
		{
			/** check for plugin dependencies **/
			if(!empty((array)$json->dependencies))
			{
				foreach ((array)$json->dependencies as $plugin_id => $version) {
					
					if($plugin_data = plugin_exists($plugin_id))
					{
						$required_version = (int)str_replace(".", "", $version);
						$existing_version = (int)str_replace(".", "", $plugin_data->version);
						if($existing_version < $required_version)
						{
							dd("Missing plugin dependency: ". $plugin_id . " version: ".$version.", Requested by plugin: ". $json->id);
							die;
						}
					}else
					{
						dd("Missing plugin dependency: ". $plugin_id . " version: ".$version.", Requested by plugin: ". $json->id);
						die;
					}
				}

			}

			/** load plugin file **/
			if(file_exists($json->index_file))
			{
				require_once $json->index_file;
				$loaded = true;
			}
		}
	}

	return $loaded;
}

/**
 * Plugins Exists.
 *
 * @param string $plugin_id
 * @return boolean|object
 */
function plugin_exists(string $plugin_id):bool|object 
{
	global $APP;
	$ids = array_column($APP['plugins'], 'id');
	$key = array_search($plugin_id, $ids);
	if($key !== false)
	{
		return $APP['plugins'][$key];
	}

	return false;
}

/**
 * Sort Plugins
 *
 * @param array $plugins
 * @return array
 */
function sort_plugins(array $plugins):array
{
	$to_sort = [];
	$sorted  = [];

	foreach ($plugins as $key => $obj) {
		$to_sort[$key] = $obj->index;
	}
	
	asort($to_sort);
	
	foreach ($to_sort as $key => $value) {
		$sorted[] = $plugins[$key];
	}

	return $sorted;
}

function valid_route(object $json):bool
{
	if(!empty($json->routes->off) && is_array($json->routes->off))
	{
		if(in_array(page(), $json->routes->off))
			return false;
	}

	if(!empty($json->routes->on) && is_array($json->routes->on))
	{
		if($json->routes->on[0] == 'all')
			return true;

		if(in_array(page(), $json->routes->on))
			return true;
	}

	return false;
}

/**
 * Add Action method.
 */

 function add_action(string $hook, mixed $func, int $priority = 10):bool
 {
 
     global $ACTIONS;
 
     while(!empty($ACTIONS[$hook][$priority])) {
         $priority++;
     }
 
     $ACTIONS[$hook][$priority] = $func;
 
     return true;
 }
 
 function do_action(string $hook, array $data = [])
 {
     global $ACTIONS;
 
     if(!empty($ACTIONS[$hook]))
     {
         ksort($ACTIONS[$hook]);
         foreach ($ACTIONS[$hook] as $key => $func) {
             $func($data);
         }
     }
 
 }
 
 function add_filter(string $hook, mixed $func, int $priority = 10):bool
 {
     global $FILTER;
 
     while(!empty($FILTER[$hook][$priority])) {
         $priority++;
     }
 
     $FILTER[$hook][$priority] = $func;
 
     return true;
 }
 
 function do_filter(string $hook, mixed $data = ''):mixed
 {
     global $FILTER;
 
     if(!empty($FILTER[$hook]))
     {
         ksort($FILTER[$hook]);
         foreach ($FILTER[$hook] as $key => $func) {
             $data = $func($data);
         }
     }
 
     return $data;
 }

 function plugin_path(string $path = '')
{
	$called_from = debug_backtrace();
	$key = array_search(__FUNCTION__, array_column($called_from, 'function'));
	return get_plugin_dir(debug_backtrace()[$key]['file']) . $path;
}

function plugin_http_path(string $path = '')
{
	$called_from = debug_backtrace();
	$key = array_search(__FUNCTION__, array_column($called_from, 'function'));
	
	return ROOT . DIRECTORY_SEPARATOR . get_plugin_dir(debug_backtrace()[$key]['file']) . $path;
}

function get_plugin_dir(string $filepath):string
{

	$path = "";

	$basename = basename($filepath);
	$path = str_replace($basename, "", $filepath);

	if(strstr($path, DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR))
	{
		$parts = explode(DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR, $path);
		$parts = explode(DIRECTORY_SEPARATOR, $parts[1]);
		$path = 'plugins' . DIRECTORY_SEPARATOR . $parts[0].DIRECTORY_SEPARATOR;

	}

	return $path;
}
