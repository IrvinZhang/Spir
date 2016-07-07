<?php
/**
 * @package		Spir
 * @date	    16/7/4
 * @author		Irvin
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Loader Class
 *
 * Loads framework components.
 */
class SP_Loader {

	// All these are set automatically. Don't mess with them.
	/**
	 * Nesting level of the output buffering mechanism
	 *
	 * @var	int
	 */
	protected $_sp_ob_level;

	/**
	 * List of paths to load views from
	 *
	 * @var	array
	 */
	protected $_sp_view_paths =	array(VIEWPATH	=> TRUE);

	/**
	 * List of paths to load libraries from
	 *
	 * @var	array
	 */
	protected $_sp_library_paths =	array(APPPATH, BASEPATH);

	/**
	 * List of paths to load models from
	 *
	 * @var	array
	 */
	protected $_sp_model_paths =	array(APPPATH);

	/**
	 * List of paths to load helpers from
	 *
	 * @var	array
	 */
	protected $_sp_helper_paths =	array(APPPATH, BASEPATH);

	/**
	 * List of cached variables
	 *
	 * @var	array
	 */
	protected $_sp_cached_vars =	array();

	/**
	 * List of loaded classes
	 *
	 * @var	array
	 */
	protected $_sp_classes =	array();

	/**
	 * List of loaded models
	 *
	 * @var	array
	 */
	protected $_sp_models =	array();

	/**
	 * List of loaded helpers
	 *
	 * @var	array
	 */
	protected $_sp_helpers =	array();

	/**
	 * List of class name mappings
	 *
	 * @var	array
	 */
	protected $_sp_varmap =	array(
		'unit_test' => 'unit',
		'user_agent' => 'agent'
	);

	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * Sets component load paths, gets the initial output buffering level.
	 *
	 * @return	void
	 */
	public function __construct()
	{
		$this->_sp_ob_level = ob_get_level();
		$this->_sp_classes =& is_loaded();
		$this->_sp_service_paths = array(APPPATH);
		log_message('info', 'Loader Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Initializer
	 *
	 * @todo	Figure out a way to move this to the constructor
	 *		without breaking *package_path*() methods.
	 * @return	void
	 */
	public function initialize()
	{
		$this->_sp_autoloader();
	}

	// --------------------------------------------------------------------

	/**
	 * Is Loaded
	 *
	 * A utility method to test if a class is in the self::$_sp_classes array.
	 *
	 * @used-by	Mainly used by Form Helper function _get_validation_object().
	 *
	 * @param 	string		$class	Class name to check for
	 * @return 	string|bool	Class object name if loaded or FALSE
	 */
	public function is_loaded($class)
	{
		return array_search(ucfirst($class), $this->_sp_classes, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * Library Loader
	 *
	 * Loads and instantiates libraries.
	 * Designed to be called from application controllers.
	 *
	 * @param	string	$library	Library name
	 * @param	array	$params		Optional parameters to pass to the library class constructor
	 * @param	string	$object_name	An optional object name to assign to
	 * @return	object
	 */
	public function library($library, $params = NULL, $object_name = NULL)
	{
		if (empty($library))
		{
			return $this;
		}
		elseif (is_array($library))
		{
			foreach ($library as $key => $value)
			{
				if (is_int($key))
				{
					$this->library($value, $params);
				}
				else
				{
					$this->library($key, $params, $value);
				}
			}

			return $this;
		}

		if ($params !== NULL && ! is_array($params))
		{
			$params = NULL;
		}

		$this->_sp_load_library($library, $params, $object_name);
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Model Loader
	 *
	 * Loads and instantiates models.
	 *
	 * @param	string	$model		Model name
	 * @param	string	$name		An optional object name to assign to
	 * @param	bool	$db_conn	An optional database connection configuration to initialize
	 * @return	object
	 */
	public function model($model, $name = '', $db_conn = FALSE)
	{
		if (empty($model))
		{
			return $this;
		}
		elseif (is_array($model))
		{
			foreach ($model as $key => $value)
			{
				is_int($key) ? $this->model($value, '', $db_conn) : $this->model($key, $value, $db_conn);
			}

			return $this;
		}

		$path = '';

		// Is the model in a sub-folder? If so, parse out the filename and path.
		if (($last_slash = strrpos($model, '/')) !== FALSE)
		{
			// The path is in front of the last slash
			$path = substr($model, 0, ++$last_slash);

			// And the model name behind it
			$model = substr($model, $last_slash);
		}

		if (empty($name))
		{
			$name = $model;
		}

		if (in_array($name, $this->_sp_models, TRUE))
		{
			return $this;
		}

		$SP =& get_instance();
		if (isset($SP->$name))
		{
			throw new RuntimeException('The model name you are loading is the name of a resource that is already being used: '.$name);
		}

		if ($db_conn !== FALSE && ! class_exists('SP_DB', FALSE))
		{
			if ($db_conn === TRUE)
			{
				$db_conn = '';
			}

			$this->database($db_conn, FALSE, TRUE);
		}

		// Note: All of the code under this condition used to be just:
		//
		//       load_class('Model', 'core');
		//
		//       However, load_class() instantiates classes
		//       to cache them for later use and that prevents
		//       MY_Model from being an abstract class and is
		//       sub-optimal otherwise anyway.
		if ( ! class_exists('SP_Model', FALSE))
		{
			$app_path = APPPATH.'core'.DIRECTORY_SEPARATOR;
			if (file_exists($app_path.'Model.php'))
			{
				require_once($app_path.'Model.php');
				if ( ! class_exists('SP_Model', FALSE))
				{
					throw new RuntimeException($app_path."Model.php exists, but doesn't declare class SP_Model");
				}
			}
			elseif ( ! class_exists('SP_Model', FALSE))
			{
				require_once(BASEPATH.'core'.DIRECTORY_SEPARATOR.'Model.php');
			}

			$class = config_item('subclass_prefix').'Model';
			if (file_exists($app_path.$class.'.php'))
			{
				require_once($app_path.$class.'.php');
				if ( ! class_exists($class, FALSE))
				{
					throw new RuntimeException($app_path.$class.".php exists, but doesn't declare class ".$class);
				}
			}
		}

		$model = ucfirst($model);
		if ( ! class_exists($model, FALSE))
		{
			foreach ($this->_sp_model_paths as $mod_path)
			{
				if ( ! file_exists($mod_path.'models/'.$path.$model.'.php'))
				{
					continue;
				}

				require_once($mod_path.'models/'.$path.$model.'.php');
				if ( ! class_exists($model, FALSE))
				{
					throw new RuntimeException($mod_path."models/".$path.$model.".php exists, but doesn't declare class ".$model);
				}

				break;
			}

			if ( ! class_exists($model, FALSE))
			{
				throw new RuntimeException('Unable to locate the model you have spespfied: '.$model);
			}
		}
		elseif ( ! is_subclass_of($model, 'SP_Model'))
		{
			throw new RuntimeException("Class ".$model." already exists and doesn't extend SP_Model");
		}

		$this->_sp_models[] = $name;
		$SP->$name = new $model();
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Database Loader
	 *
	 * @param	mixed	$params		Database configuration options
	 * @param	bool	$return 	Whether to return the database object
	 * @param	bool	$query_builder	Whether to enable Query Builder
	 *					(overrides the configuration setting)
	 *
	 * @return	object|bool	Database object if $return is set to TRUE,
	 *					FALSE on failure, SP_Loader instance in any other case
	 */
	public function database($params = '', $return = FALSE, $query_builder = NULL)
	{
		// Grab the super object
		$SP =& get_instance();

		// Do we even need to load the database class?
		if ($return === FALSE && $query_builder === NULL && isset($SP->db) && is_object($SP->db) && ! empty($SP->db->conn_id))
		{
			return FALSE;
		}

		require_once(BASEPATH.'database/DB.php');

		if ($return === TRUE)
		{
			return DB($params, $query_builder);
		}

		// Initialize the db variable. Needed to prevent
		// reference errors with some configurations
		$SP->db = '';

		// Load the DB class
		$SP->db =& DB($params, $query_builder);
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Load the Database Utilities Class
	 *
	 * @param	object	$db	Database object
	 * @param	bool	$return	Whether to return the DB Utilities class object or not
	 * @return	object
	 */
	public function dbutil($db = NULL, $return = FALSE)
	{
		$SP =& get_instance();

		if ( ! is_object($db) OR ! ($db instanceof SP_DB))
		{
			class_exists('SP_DB', FALSE) OR $this->database();
			$db =& $SP->db;
		}

		require_once(BASEPATH.'database/DB_utility.php');
		require_once(BASEPATH.'database/drivers/'.$db->dbdriver.'/'.$db->dbdriver.'_utility.php');
		$class = 'SP_DB_'.$db->dbdriver.'_utility';

		if ($return === TRUE)
		{
			return new $class($db);
		}

		$SP->dbutil = new $class($db);
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Load the Database Forge Class
	 *
	 * @param	object	$db	Database object
	 * @param	bool	$return	Whether to return the DB Forge class object or not
	 * @return	object
	 */
	public function dbforge($db = NULL, $return = FALSE)
	{
		$SP =& get_instance();
		if ( ! is_object($db) OR ! ($db instanceof SP_DB))
		{
			class_exists('SP_DB', FALSE) OR $this->database();
			$db =& $SP->db;
		}

		require_once(BASEPATH.'database/DB_forge.php');
		require_once(BASEPATH.'database/drivers/'.$db->dbdriver.'/'.$db->dbdriver.'_forge.php');

		if ( ! empty($db->subdriver))
		{
			$driver_path = BASEPATH.'database/drivers/'.$db->dbdriver.'/subdrivers/'.$db->dbdriver.'_'.$db->subdriver.'_forge.php';
			if (file_exists($driver_path))
			{
				require_once($driver_path);
				$class = 'SP_DB_'.$db->dbdriver.'_'.$db->subdriver.'_forge';
			}
		}
		else
		{
			$class = 'SP_DB_'.$db->dbdriver.'_forge';
		}

		if ($return === TRUE)
		{
			return new $class($db);
		}

		$SP->dbforge = new $class($db);
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * View Loader
	 *
	 * Loads "view" files.
	 *
	 * @param	string	$view	View name
	 * @param	array	$vars	An assospative array of data
	 *				to be extracted for use in the view
	 * @param	bool	$return	Whether to return the view output
	 *				or leave it to the Output class
	 * @return	object|string
	 */
	public function view($view, $vars = array(), $return = FALSE)
	{
		return $this->_sp_load(array('_sp_view' => $view, '_sp_vars' => $this->_sp_object_to_array($vars), '_sp_return' => $return));
	}

	// --------------------------------------------------------------------

	/**
	 * Generic File Loader
	 *
	 * @param	string	$path	File path
	 * @param	bool	$return	Whether to return the file output
	 * @return	object|string
	 */
	public function file($path, $return = FALSE)
	{
		return $this->_sp_load(array('_sp_path' => $path, '_sp_return' => $return));
	}

	// --------------------------------------------------------------------

	/**
	 * Set Variables
	 *
	 * Once variables are set they become available within
	 * the controller class and its "view" files.
	 *
	 * @param	array|object|string	$vars
	 *					An assospative array or object containing values
	 *					to be set, or a value's name if string
	 * @param 	string	$val	Value to set, only used if $vars is a string
	 * @return	object
	 */
	public function vars($vars, $val = '')
	{
		if (is_string($vars))
		{
			$vars = array($vars => $val);
		}

		$vars = $this->_sp_object_to_array($vars);

		if (is_array($vars) && count($vars) > 0)
		{
			foreach ($vars as $key => $val)
			{
				$this->_sp_cached_vars[$key] = $val;
			}
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Clear Cached Variables
	 *
	 * Clears the cached variables.
	 *
	 * @return	SP_Loader
	 */
	public function clear_vars()
	{
		$this->_sp_cached_vars = array();
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Variable
	 *
	 * Check if a variable is set and retrieve it.
	 *
	 * @param	string	$key	Variable name
	 * @return	mixed	The variable or NULL if not found
	 */
	public function get_var($key)
	{
		return isset($this->_sp_cached_vars[$key]) ? $this->_sp_cached_vars[$key] : NULL;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Variables
	 *
	 * Retrieves all loaded variables.
	 *
	 * @return	array
	 */
	public function get_vars()
	{
		return $this->_sp_cached_vars;
	}

	// --------------------------------------------------------------------

	/**
	 * Helper Loader
	 *
	 * @param	string|string[]	$helpers	Helper name(s)
	 * @return	object
	 */
	public function helper($helpers = array())
	{
		foreach ($this->_sp_prep_filename($helpers, '_helper') as $helper)
		{
			if (isset($this->_sp_helpers[$helper]))
			{
				continue;
			}

			// Is this a helper extension request?
			$ext_helper = config_item('subclass_prefix').$helper;
			$ext_loaded = FALSE;
			foreach ($this->_sp_helper_paths as $path)
			{
				if (file_exists($path.'helpers/'.$ext_helper.'.php'))
				{
					include_once($path.'helpers/'.$ext_helper.'.php');
					$ext_loaded = TRUE;
				}
			}

			// If we have loaded extensions - check if the base one is here
			if ($ext_loaded === TRUE)
			{
				$base_helper = BASEPATH.'helpers/'.$helper.'.php';
				if ( ! file_exists($base_helper))
				{
					show_error('Unable to load the requested file: helpers/'.$helper.'.php');
				}

				include_once($base_helper);
				$this->_sp_helpers[$helper] = TRUE;
				log_message('info', 'Helper loaded: '.$helper);
				continue;
			}

			// No extensions found ... try loading regular helpers and/or overrides
			foreach ($this->_sp_helper_paths as $path)
			{
				if (file_exists($path.'helpers/'.$helper.'.php'))
				{
					include_once($path.'helpers/'.$helper.'.php');

					$this->_sp_helpers[$helper] = TRUE;
					log_message('info', 'Helper loaded: '.$helper);
					break;
				}
			}

			// unable to load the helper
			if ( ! isset($this->_sp_helpers[$helper]))
			{
				show_error('Unable to load the requested file: helpers/'.$helper.'.php');
			}
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Load Helpers
	 *
	 * An alias for the helper() method in case the developer has
	 * written the plural form of it.
	 *
	 * @param	string|string[]	$helpers	Helper name(s)
	 * @return	object
	 */
	public function helpers($helpers = array())
	{
		return $this->helper($helpers);
	}

	// --------------------------------------------------------------------

	/**
	 * Language Loader
	 *
	 * Loads language files.
	 *
	 * @param	string|string[]	$files	List of language file names to load
	 * @param	string		Language name
	 * @return	object
	 */
	public function language($files, $lang = '')
	{
		get_instance()->lang->load($files, $lang);
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Config Loader
	 *
	 * Loads a config file (an alias for SP_Config::load()).
	 *
	 * @param	string	$file			Configuration file name
	 * @param	bool	$use_sections		Whether configuration values should be loaded into their own section
	 * @param	bool	$fail_gracefully	Whether to just return FALSE or display an error message
	 * @return	bool	TRUE if the file was loaded correctly or FALSE on failure
	 */
	public function config($file, $use_sections = FALSE, $fail_gracefully = FALSE)
	{
		return get_instance()->config->load($file, $use_sections, $fail_gracefully);
	}

	// --------------------------------------------------------------------

	/**
	 * Driver Loader
	 *
	 * Loads a driver library.
	 *
	 * @param	string|string[]	$library	Driver name(s)
	 * @param	array		$params		Optional parameters to pass to the driver
	 * @param	string		$object_name	An optional object name to assign to
	 *
	 * @return	object|bool	Object or FALSE on failure if $library is a string
	 *				and $object_name is set. SP_Loader instance otherwise.
	 */
	public function driver($library, $params = NULL, $object_name = NULL)
	{
		if (is_array($library))
		{
			foreach ($library as $key => $value)
			{
				if (is_int($key))
				{
					$this->driver($value, $params);
				}
				else
				{
					$this->driver($key, $params, $value);
				}
			}

			return $this;
		}
		elseif (empty($library))
		{
			return FALSE;
		}

		if ( ! class_exists('SP_Driver_Library', FALSE))
		{
			// We aren't instantiating an object here, just making the base class available
			require BASEPATH.'libraries/Driver.php';
		}

		// We can save the loader some time since Drivers will *always* be in a subfolder,
		// and typically identically named to the library
		if ( ! strpos($library, '/'))
		{
			$library = ucfirst($library).'/'.$library;
		}

		return $this->library($library, $params, $object_name);
	}

	// --------------------------------------------------------------------

	/**
	 * Add Package Path
	 *
	 * Prepends a parent path to the library, model, helper and config
	 * path arrays.
	 *
	 * @param	string	$path		Path to add
	 * @param 	bool	$view_cascade	(default: TRUE)
	 * @return	object
	 */
	public function add_package_path($path, $view_cascade = TRUE)
	{
		$path = rtrim($path, '/').'/';

		array_unshift($this->_sp_library_paths, $path);
		array_unshift($this->_sp_model_paths, $path);
		array_unshift($this->_sp_helper_paths, $path);

		$this->_sp_view_paths = array($path.'views/' => $view_cascade) + $this->_sp_view_paths;

		// Add config file path
		$config =& $this->_sp_get_component('config');
		$config->_config_paths[] = $path;

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Package Paths
	 *
	 * Return a list of all package paths.
	 *
	 * @param	bool	$include_base	Whether to include BASEPATH (default: FALSE)
	 * @return	array
	 */
	public function get_package_paths($include_base = FALSE)
	{
		return ($include_base === TRUE) ? $this->_sp_library_paths : $this->_sp_model_paths;
	}

	// --------------------------------------------------------------------

	/**
	 * Remove Package Path
	 *
	 * Remove a path from the library, model, helper and/or config
	 * path arrays if it exists. If no path is provided, the most recently
	 * added path will be removed removed.
	 *
	 * @param	string	$path	Path to remove
	 * @return	object
	 */
	public function remove_package_path($path = '')
	{
		$config =& $this->_sp_get_component('config');

		if ($path === '')
		{
			array_shift($this->_sp_library_paths);
			array_shift($this->_sp_model_paths);
			array_shift($this->_sp_helper_paths);
			array_shift($this->_sp_view_paths);
			array_pop($config->_config_paths);
		}
		else
		{
			$path = rtrim($path, '/').'/';
			foreach (array('_sp_library_paths', '_sp_model_paths', '_sp_helper_paths') as $var)
			{
				if (($key = array_search($path, $this->{$var})) !== FALSE)
				{
					unset($this->{$var}[$key]);
				}
			}

			if (isset($this->_sp_view_paths[$path.'views/']))
			{
				unset($this->_sp_view_paths[$path.'views/']);
			}

			if (($key = array_search($path, $config->_config_paths)) !== FALSE)
			{
				unset($config->_config_paths[$key]);
			}
		}

		// make sure the application default paths are still in the array
		$this->_sp_library_paths = array_unique(array_merge($this->_sp_library_paths, array(APPPATH, BASEPATH)));
		$this->_sp_helper_paths = array_unique(array_merge($this->_sp_helper_paths, array(APPPATH, BASEPATH)));
		$this->_sp_model_paths = array_unique(array_merge($this->_sp_model_paths, array(APPPATH)));
		$this->_sp_view_paths = array_merge($this->_sp_view_paths, array(APPPATH.'views/' => TRUE));
		$config->_config_paths = array_unique(array_merge($config->_config_paths, array(APPPATH)));

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Internal SP Data Loader
	 *
	 * Used to load views and files.
	 *
	 * Variables are prefixed with _sp_ to avoid symbol collision with
	 * variables made available to view files.
	 *
	 * @param	array	$_sp_data	Data to load
	 * @return	object
	 */
	protected function _sp_load($_sp_data)
	{
		// Set the default data variables
		foreach (array('_sp_view', '_sp_vars', '_sp_path', '_sp_return') as $_sp_val)
		{
			$$_sp_val = $_sp_data[$_sp_val] ?? FALSE;
		}

		$file_exists = FALSE;

		// Set the path to the requested file
		if (is_string($_sp_path) && $_sp_path !== '')
		{
			$_sp_x = explode('/', $_sp_path);
			$_sp_file = end($_sp_x);
		}
		else
		{
			$_sp_ext = pathinfo($_sp_view, PATHINFO_EXTENSION);
			$_sp_file = ($_sp_ext === '') ? $_sp_view.'.php' : $_sp_view;

			foreach ($this->_sp_view_paths as $_sp_view_file => $cascade)
			{
				if (file_exists($_sp_view_file.$_sp_file))
				{
					$_sp_path = $_sp_view_file.$_sp_file;
					$file_exists = TRUE;
					break;
				}

				if ( ! $cascade)
				{
					break;
				}
			}
		}

		if ( ! $file_exists && ! file_exists($_sp_path))
		{
			show_error('Unable to load the requested file: '.$_sp_file);
		}

		// This allows anything loaded using $this->load (views, files, etc.)
		// to become accessible from within the Controller and Model functions.
		$_sp_SP =& get_instance();
		foreach (get_object_vars($_sp_SP) as $_sp_key => $_sp_var)
		{
			if ( ! isset($this->$_sp_key))
			{
				$this->$_sp_key =& $_sp_SP->$_sp_key;
			}
		}

		/*
		 * Extract and cache variables
		 *
		 * You can either set variables using the dedicated $this->load->vars()
		 * function or via the second parameter of this function. We'll merge
		 * the two types and cache them so that views that are embedded within
		 * other views can have access to these variables.
		 */
		if (is_array($_sp_vars))
		{
			foreach (array_keys($_sp_vars) as $key)
			{
				if (strncmp($key, '_sp_', 4) === 0)
				{
					unset($_sp_vars[$key]);
				}
			}

			$this->_sp_cached_vars = array_merge($this->_sp_cached_vars, $_sp_vars);
		}
		extract($this->_sp_cached_vars);

		/*
		 * Buffer the output
		 *
		 * We buffer the output for two reasons:
		 * 1. Speed. You get a significant speed boost.
		 * 2. So that the final rendered template can be post-processed by
		 *	the output class. Why do we need post processing? For one thing,
		 *	in order to show the elapsed page load time. Unless we can
		 *	intercept the content right before it's sent to the browser and
		 *	then stop the timer it won't be accurate.
		 */
		ob_start();

		// If the PHP installation does not support short tags we'll
		// do a little string replacement, changing the short tags
		// to standard PHP echo statements.
		if ( ! ini_get('short_open_tag') && config_item('rewrite_short_tags') === TRUE)
		{
			echo eval('?>'.preg_replace('/;*\s*\?>/', '; ?>', str_replace('<?=', '<?php echo ', file_get_contents($_sp_path))));
		}
		else
		{
			include($_sp_path); // include() vs include_once() allows for multiple views with the same name
		}
		
		log_message('info', 'File loaded: '.$_sp_path);

		// Return the file data if requested
		if ($_sp_return === TRUE)
		{
			$buffer = ob_get_contents();
			@ob_end_clean();
			return $buffer;
		}

		/*
		 * Flush the buffer... or buff the flusher?
		 *
		 * In order to permit views to be nested within
		 * other views, we need to flush the content back out whenever
		 * we are beyond the first level of output buffering so that
		 * it can be seen and included properly by the first included
		 * template and any subsequent ones. Oy!
		 */
		if (ob_get_level() > $this->_sp_ob_level + 1)
		{
			ob_end_flush();
		}
		else
		{
			$_sp_SP->output->append_output(ob_get_contents());
			@ob_end_clean();
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Internal SP Library Loader
	 *
	 * @used-by	SP_Loader::library()
	 * @uses	SP_Loader::_sp_init_library()
	 *
	 * @param	string	$class		Class name to load
	 * @param	mixed	$params		Optional parameters to pass to the class constructor
	 * @param	string	$object_name	Optional object name to assign to
	 * @return	void
	 */
	protected function _sp_load_library($class, $params = NULL, $object_name = NULL)
	{
		// Get the class name, and while we're at it trim any slashes.
		// The directory path can be included as part of the class name,
		// but we don't want a leading slash
		$class = str_replace('.php', '', trim($class, '/'));

		// Was the path included with the class name?
		// We look for a slash to determine this
		if (($last_slash = strrpos($class, '/')) !== FALSE)
		{
			// Extract the path
			$subdir = substr($class, 0, ++$last_slash);

			// Get the filename from the path
			$class = substr($class, $last_slash);
		}
		else
		{
			$subdir = '';
		}

		$class = ucfirst($class);

		// Is this a stock library? There are a few spespal conditions if so ...
		if (file_exists(BASEPATH.'libraries/'.$subdir.$class.'.php'))
		{
			return $this->_sp_load_stock_library($class, $subdir, $params, $object_name);
		}

		// Let's search for the requested library file and load it.
		foreach ($this->_sp_library_paths as $path)
		{
			// BASEPATH has already been checked for
			if ($path === BASEPATH)
			{
				continue;
			}

			$filepath = $path.'libraries/'.$subdir.$class.'.php';

			// Safety: Was the class already loaded by a previous call?
			if (class_exists($class, FALSE))
			{
				// Before we deem this to be a duplicate request, let's see
				// if a custom object name is being supplied. If so, we'll
				// return a new instance of the object
				if ($object_name !== NULL)
				{
					$SP =& get_instance();
					if ( ! isset($SP->$object_name))
					{
						return $this->_sp_init_library($class, '', $params, $object_name);
					}
				}

				log_message('debug', $class.' class already loaded. Second attempt ignored.');
				return;
			}
			// Does the file exist? No? Bummer...
			elseif ( ! file_exists($filepath))
			{
				continue;
			}

			include_once($filepath);
			return $this->_sp_init_library($class, '', $params, $object_name);
		}

		// One last attempt. Maybe the library is in a subdirectory, but it wasn't spespfied?
		if ($subdir === '')
		{
			return $this->_sp_load_library($class.'/'.$class, $params, $object_name);
		}

		// If we got this far we were unable to find the requested class.
		log_message('error', 'Unable to load the requested class: '.$class);
		show_error('Unable to load the requested class: '.$class);
	}

	// --------------------------------------------------------------------

	/**
	 * Internal SP Stock Library Loader
	 *
	 * @used-by	SP_Loader::_sp_load_library()
	 * @uses	SP_Loader::_sp_init_library()
	 *
	 * @param	string	$library	Library name to load
	 * @param	string	$file_path	Path to the library filename, relative to libraries/
	 * @param	mixed	$params		Optional parameters to pass to the class constructor
	 * @param	string	$object_name	Optional object name to assign to
	 * @return	void
	 */
	protected function _sp_load_stock_library($library_name, $file_path, $params, $object_name)
	{
		$prefix = 'SP_';

		if (class_exists($prefix.$library_name, FALSE))
		{
			if (class_exists(config_item('subclass_prefix').$library_name, FALSE))
			{
				$prefix = config_item('subclass_prefix');
			}

			// Before we deem this to be a duplicate request, let's see
			// if a custom object name is being supplied. If so, we'll
			// return a new instance of the object
			if ($object_name !== NULL)
			{
				$SP =& get_instance();
				if ( ! isset($SP->$object_name))
				{
					return $this->_sp_init_library($library_name, $prefix, $params, $object_name);
				}
			}

			log_message('debug', $library_name.' class already loaded. Second attempt ignored.');
			return;
		}

		$paths = $this->_sp_library_paths;
		array_pop($paths); // BASEPATH
		array_pop($paths); // APPPATH (needs to be the first path checked)
		array_unshift($paths, APPPATH);

		foreach ($paths as $path)
		{
			if (file_exists($path = $path.'libraries/'.$file_path.$library_name.'.php'))
			{
				// Override
				include_once($path);
				if (class_exists($prefix.$library_name, FALSE))
				{
					return $this->_sp_init_library($library_name, $prefix, $params, $object_name);
				}
				else
				{
					log_message('debug', $path.' exists, but does not declare '.$prefix.$library_name);
				}
			}
		}

		include_once(BASEPATH.'libraries/'.$file_path.$library_name.'.php');

		// Check for extensions
		$subclass = config_item('subclass_prefix').$library_name;
		foreach ($paths as $path)
		{
			if (file_exists($path = $path.'libraries/'.$file_path.$subclass.'.php'))
			{
				include_once($path);
				if (class_exists($subclass, FALSE))
				{
					$prefix = config_item('subclass_prefix');
					break;
				}
				else
				{
					log_message('debug', $path.' exists, but does not declare '.$subclass);
				}
			}
		}

		return $this->_sp_init_library($library_name, $prefix, $params, $object_name);
	}

	// --------------------------------------------------------------------

	/**
	 * Internal SP Library Instantiator
	 *
	 * @used-by	SP_Loader::_sp_load_stock_library()
	 * @used-by	SP_Loader::_sp_load_library()
	 *
	 * @param	string		$class		Class name
	 * @param	string		$prefix		Class name prefix
	 * @param	array|null|bool	$config		Optional configuration to pass to the class constructor:
	 *						FALSE to skip;
	 *						NULL to search in config paths;
	 *						array containing configuration data
	 * @param	string		$object_name	Optional object name to assign to
	 * @return	void
	 */
	protected function _sp_init_library($class, $prefix, $config = FALSE, $object_name = NULL)
	{
		// Is there an associated config file for this class? Note: these should always be lowercase
		if ($config === NULL)
		{
			// Fetch the config paths containing any package paths
			$config_component = $this->_sp_get_component('config');

			if (is_array($config_component->_config_paths))
			{
				$found = FALSE;
				foreach ($config_component->_config_paths as $path)
				{
					// We test for both uppercase and lowercase, for servers that
					// are case-sensitive with regard to file names. Load global first,
					// override with environment next
					if (file_exists($path.'config/'.strtolower($class).'.php'))
					{
						include($path.'config/'.strtolower($class).'.php');
						$found = TRUE;
					}
					elseif (file_exists($path.'config/'.ucfirst(strtolower($class)).'.php'))
					{
						include($path.'config/'.ucfirst(strtolower($class)).'.php');
						$found = TRUE;
					}

					if (file_exists($path.'config/'.ENVIRONMENT.'/'.strtolower($class).'.php'))
					{
						include($path.'config/'.ENVIRONMENT.'/'.strtolower($class).'.php');
						$found = TRUE;
					}
					elseif (file_exists($path.'config/'.ENVIRONMENT.'/'.ucfirst(strtolower($class)).'.php'))
					{
						include($path.'config/'.ENVIRONMENT.'/'.ucfirst(strtolower($class)).'.php');
						$found = TRUE;
					}

					// Break on the first found configuration, thus package
					// files are not overridden by default paths
					if ($found === TRUE)
					{
						break;
					}
				}
			}
		}

		$class_name = $prefix.$class;

		// Is the class name valid?
		if ( ! class_exists($class_name, FALSE))
		{
			log_message('error', 'Non-existent class: '.$class_name);
			show_error('Non-existent class: '.$class_name);
		}

		// Set the variable name we will assign the class to
		// Was a custom class name supplied? If so we'll use it
		if (empty($object_name))
		{
			$object_name = strtolower($class);
			if (isset($this->_sp_varmap[$object_name]))
			{
				$object_name = $this->_sp_varmap[$object_name];
			}
		}

		// Don't overwrite existing properties
		$SP =& get_instance();
		if (isset($SP->$object_name))
		{
			if ($SP->$object_name instanceof $class_name)
			{
				log_message('debug', $class_name." has already been instantiated as '".$object_name."'. Second attempt aborted.");
				return;
			}

			show_error("Resource '".$object_name."' already exists and is not a ".$class_name." instance.");
		}

		// Save the class name and object name
		$this->_sp_classes[$object_name] = $class;

		// Instantiate the class
		$SP->$object_name = isset($config)
			? new $class_name($config)
			: new $class_name();
	}

	// --------------------------------------------------------------------

	/**
	 * SP Autoloader
	 *
	 * Loads component listed in the config/autoload.php file.
	 *
	 * @used-by	SP_Loader::initialize()
	 * @return	void
	 */
	protected function _sp_autoloader()
	{
		if (file_exists(APPPATH.'config/autoload.php'))
		{
			include(APPPATH.'config/autoload.php');
		}

		if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/autoload.php'))
		{
			include(APPPATH.'config/'.ENVIRONMENT.'/autoload.php');
		}

		if ( ! isset($autoload))
		{
			return;
		}

		// Autoload packages
		if (isset($autoload['packages']))
		{
			foreach ($autoload['packages'] as $package_path)
			{
				$this->add_package_path($package_path);
			}
		}

		// Load any custom config file
		if (count($autoload['config']) > 0)
		{
			foreach ($autoload['config'] as $val)
			{
				$this->config($val);
			}
		}

		// Autoload helpers and languages
		foreach (array('helper', 'language') as $type)
		{
			if (isset($autoload[$type]) && count($autoload[$type]) > 0)
			{
				$this->$type($autoload[$type]);
			}
		}

		// Autoload drivers
		if (isset($autoload['drivers']))
		{
			$this->driver($autoload['drivers']);
		}

		// Load libraries
		if (isset($autoload['libraries']) && count($autoload['libraries']) > 0)
		{
			// Load the database driver.
			if (in_array('database', $autoload['libraries']))
			{
				$this->database();
				$autoload['libraries'] = array_diff($autoload['libraries'], array('database'));
			}

			// Load all other libraries
			$this->library($autoload['libraries']);
		}

		// Autoload models
		if (isset($autoload['model']))
		{
			$this->model($autoload['model']);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * SP Object to Array translator
	 *
	 * Takes an object as input and converts the class variables to
	 * an assospative array with key/value pairs.
	 *
	 * @param	object	$object	Object data to translate
	 * @return	array
	 */
	protected function _sp_object_to_array($object)
	{
		return is_object($object) ? get_object_vars($object) : $object;
	}

	// --------------------------------------------------------------------

	/**
	 * SP Component getter
	 *
	 * Get a reference to a spespfic library or model.
	 *
	 * @param 	string	$component	Component name
	 * @return	bool
	 */
	protected function &_sp_get_component($component)
	{
		$SP =& get_instance();
		return $SP->$component;
	}

	// --------------------------------------------------------------------

	/**
	 * Prep filename
	 *
	 * This function prepares filenames of various items to
	 * make their loading more reliable.
	 *
	 * @param	string|string[]	$filename	Filename(s)
	 * @param 	string		$extension	Filename extension
	 * @return	array
	 */
	protected function _sp_prep_filename($filename, $extension)
	{
		if ( ! is_array($filename))
		{
			return array(strtolower(str_replace(array($extension, '.php'), '', $filename).$extension));
		}
		else
		{
			foreach ($filename as $key => $val)
			{
				$filename[$key] = strtolower(str_replace(array($extension, '.php'), '', $val).$extension);
			}

			return $filename;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Service Loader
	 *
	 * This function lets users load and instantiate classes.
	 * It is designed to be called from a user's app controllers.
	 *
	 * @param string the name of the class
	 * @param mixed the optional parameters
	 * @param string an optional object name
	 * @return void
	 */
	public function service($service = '', $params = NULL, $object_name = NULL)
	{
		if (is_array($service)) {
			foreach ($service as $class) {
				$this->service($class, $params);
			}

			return;
		}

		if ($service == '' or isset($this->_sp_services[$service])) {
			return FALSE;
		}

		if (!is_null($params) && !is_array($params)) {
			$params = NULL;
		}

		$subdir = '';

		// Is the service in a sub-folder? If so, parse out the filename and path.
		if (($last_slash = strrpos($service, '/')) !== FALSE) {
			// The path is in front of the last slash
			$subdir = substr($service, 0, $last_slash + 1);

			// And the service name behind it
			$service = substr($service, $last_slash + 1);
		}

		if (!class_exists('SP_Service', FALSE)) {
			load_class('Service', 'core');
		}

		foreach ($this->_sp_service_paths as $path) {

			$filepath = $path . 'services/' . $subdir . $service . '.php';

			if (!file_exists($filepath)) {
				continue;
			}

			include_once($filepath);

			$service = strtolower($service);

			if (empty($object_name)) {
				$object_name = $service;
			}

			$service = ucfirst($service);
			$SP = &get_instance();
			if ($params !== NULL) {
				$SP->$object_name = new $service($params);
			} else {
				$SP->$object_name = new $service();
			}

			$this->_sp_services[] = $object_name;

			return;
		}
	}

}
