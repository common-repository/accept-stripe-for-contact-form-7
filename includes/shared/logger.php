<?php
namespace CF7PA_Pay_Addons\Shared;
class Logger {


	/**
	 * $log_file - path and log file name
	 * @var string
	 */
	public static $log_file;

	/**
	 * $file - file
	 * @var string
	 */
	protected static $file;

	/**
	 * $options - settable options
	 * @var array $dateFormat of the format used for the log.txt file; $logFormat used for the time of a single log event
	 */
	protected static $options = array(
		'dateFormat' => 'Y-m-d',
		'logFormat'  => 'Y-m-d H:i:s',
	);

	private static $instance;

	/**
	 * Create the log file
	 * @param string $log_file - path and filename of log
	 * @param array $params - settable options
	 */
	public static function createLogFile() {
		$time             = gmdate( static::$options['dateFormat'] );
		static::$log_file = CF7PA_ADDONS_LOG_FOLDER . "/{$time}.log";

		//Check if directory /logs exists
		if ( ! file_exists( CF7PA_ADDONS_LOG_FOLDER ) ) {
			mkdir( CF7PA_ADDONS_LOG_FOLDER, 0777, true );
		}

		//Create log file if it doesn't exist.
		if ( ! file_exists( static::$log_file ) ) {
			fopen( static::$log_file, 'w' ) or exit( "Can't create {static::log_file}!" );
		}

		//Check permissions of file.
		if ( ! is_writable( static::$log_file ) ) {
			//throw exception if not writable
			throw new Exception( 'ERROR: Unable to write to file!', 1 );
		}
	}

	/**
	 * Set logging options (optional)
	 * @param array $options Array of settable options
	 *
	 * Options:
	 *  [
	 *      'dateFormat' => 'value of the date format the .txt file should be saved int'
	 *      'logFormat' => 'value of the date format each log event should be saved int'
	 *  ]
	 */
	public static function setOptions( $options = array() ) {
		static::$options = array_merge( static::$options, $options );
	}

	/**
	 * Info method (write info message)
	 *
	 * Used for e.g.: "The user example123 has created a post".
	 *
	 * @param string $message Descriptive text of the debug
	 * @param string $context Array to expend the message's meaning
	 * @return void
	 */
	public static function info( $message, $context = array() ) {
		// grab the line and file path where the log method has been executed ( for troubleshooting )
		$bt = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 1 );

		//execute the writeLog method with passing the arguments
		static::writeLog(
			array(
				'message'  => $message,
				'bt'       => $bt,
				'severity' => 'INFO',
				'context'  => $context,
			)
		);
	}

	/**
	 * Notice method (write notice message)
	 *
	 * Used for e.g.: "The user example123 has created a post".
	 *
	 * @param string $message Descriptive text of the debug
	 * @param string $context Array to expend the message's meaning
	 * @return void
	 */
	public static function notice( $message, $context = array() ) {
		// grab the line and file path where the log method has been executed ( for troubleshooting )
		$bt = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 1 );

		//execute the writeLog method with passing the arguments
		static::writeLog(
			array(
				'message'  => $message,
				'bt'       => $bt,
				'severity' => 'NOTICE',
				'context'  => $context,
			)
		);
	}

	/**
	 * Debug method (write debug message)
	 *
	 * Used for debugging, could be used instead of echo'ing values
	 *
	 * @param string $message Descriptive text of the debug
	 * @param string $context Array to expend the message's meaning
	 * @return void
	 */
	public static function debug( $message, $context = array() ) {

		// grab the line and file path where the log method has been executed ( for troubleshooting )
		$bt = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 1 );

		//execute the writeLog method with passing the arguments
		static::writeLog(
			array(
				'message'  => $message,
				'bt'       => $bt,
				'severity' => 'DEBUG',
				'context'  => $context,
			)
		);
	}

	/**
	 * Warning method (write warning message)
	 *
	 * Used for warnings which is not fatal to the current operation
	 *
	 * @param string $message Descriptive text of the warning
	 * @param string $context Array to expend the message's meaning
	 * @return void
	 */
	public static function warning( $message, $context = array() ) {
		// grab the line and file path where the log method has been executed ( for troubleshooting )
		$bt = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 1 );

		//execute the writeLog method with passing the arguments
		static::writeLog(
			array(
				'message'  => $message,
				'bt'       => $bt,
				'severity' => 'WARNING',
				'context'  => $context,
			)
		);
	}

	/**
	 * Error method (write error message)
	 *
	 * Used for e.g. file not found,...
	 *
	 * @param string $message Descriptive text of the error
	 * @param string $context Array to expend the message's meaning
	 * @return void
	 */
	public static function error( $message, $context = array() ) {
		// grab the line and file path where the log method has been executed ( for troubleshooting )
		$bt = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 1 );

		//execute the writeLog method with passing the arguments
		static::writeLog(
			array(
				'message'  => $message,
				'bt'       => $bt,
				'severity' => 'ERROR',
				'context'  => $context,
			)
		);
	}

	/**
	 * Fatal method (write fatal message)
	 *
	 * Used for e.g. database unavailable, system shutdown
	 *
	 * @param string $message Descriptive text of the error
	 * @param string $context Array to expend the message's meaning
	 * @return void
	 */
	public static function fatal( $message, $context = array() ) {
		// grab the line and file path where the log method has been executed ( for troubleshooting )
		$bt = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 1 );

		//execute the writeLog method with passing the arguments
		static::writeLog(
			array(
				'message'  => $message,
				'bt'       => $bt,
				'severity' => 'FATAL',
				'context'  => $context,
			)
		);
	}

	/**
	 * Write to log file
	 * @param array $args Array of message (for log file), line (of log method execution), severity (for log file) and displayMessage (to display on frontend for the used)
	 * @return void
	 */
	// public function writeLog($message, $line = null, $displayMessage = null, $severity)
	public static function writeLog( $args = array() ) {
		$sys_settings = get_option( 'cf7pa_sys_settings', array() );
		if ( array_key_exists( 'cf7pa_sys_settings', $sys_settings ) && ! $sys_settings['enable_logging'] ) {
			return;
		}

		//Create the log file
		static::createLogFile();

		// open log file
		if ( ! is_resource( static::$file ) ) {
			static::openLog();
		}

		// // grab the url path
		// $path = $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

		//Grab time - based on the time format
		$time = gmdate( static::$options['logFormat'] );

		// Convert context to json
		$context = wp_json_encode( $args['context'] );

		$caller = array_shift( $args['bt'] );
		$btLine = $caller['line'];
		$btPath = $caller['file'];

		$path = basename( $btPath );

		// Create log variable = value pairs
		$timeLog     = is_null( $time ) ? '[N/A] ' : "[{$time}] ";
		$pathLog     = is_null( $path ) ? '[N/A] ' : "[{$path}] ";
		$lineLog     = is_null( $btLine ) ? '[N/A] ' : "[{$btLine}] ";
		$severityLog = is_null( $args['severity'] ) ? '[N/A]' : "[{$args['severity']}]";
		$messageLog  = is_null( $args['message'] ) ? 'N/A' : "{$args['message']}";
		$contextLog  = empty( $args['context'] ) ? '' : "{$context}";

		// Write time, url, & message to end of file
		fwrite( static::$file, "{$timeLog}{$pathLog}{$lineLog}: {$severityLog} - {$messageLog} {$contextLog}" . PHP_EOL );

		// Close file stream
		static::closeFile();
	}

	/**
	 * Open log file
	 * @return void
	 */
	private static function openLog() {
		$openFile = static::$log_file;
		// 'a' option = place pointer at end of file
		static::$file = fopen( $openFile, 'a' ) or exit( "Can't open $openFile!" );
	}

	/**
	 *  Close file stream
	 */
	public static function closeFile() {
		if ( static::$file ) {
			fclose( static::$file );
		}
	}

	/**
	 * The Singleton's constructor should always be private to prevent direct
	 * construction calls with the `new` operator.
	 */
	protected function __construct() {
	}

	/**
	 * Singletons should not be cloneable.
	 */
	protected function __clone() {
	}

	/**
	 * Singletons should not be restorable from strings.
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize a singleton.' );
	}

	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * The Singleton's constructor should always be private to prevent direct
	 * construction calls with the `new` operator.
	 */
	private function __destruct() {
	}
}
