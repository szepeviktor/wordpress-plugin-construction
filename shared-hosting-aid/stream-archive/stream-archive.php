<?php
/*
Snippet name: Archive all a directory tree
Description: gmp extention is necessary.
*/

archive_stream_zip_directory( dirname( __FILE__ ), 'web-files.zip' );

function archive_stream_zip_directory( $root, $archive = 'archive.zip' ) {
    ini_set( 'display_errors', '1' );
    ini_set( 'display_startup_errors', '1' );
    date_default_timezone_set( 'UTC' );

    if ( ! class_exists( 'RecursiveIteratorIterator' )
        || ! function_exists( 'gzdeflate' ) )
        return 1;

    if ( ! file_exists( $root ) )
        return 2;

    if ( function_exists( 'proc_nice' ) )
        proc_nice( 19 );

    $files = array();
    $added = array();
    $objects = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root ), RecursiveIteratorIterator::SELF_FIRST );
    $zip = new ArchiveStream_Zip( $archive );

    foreach ( $objects as $name => $object ) {
        // http://php.net/manual/en/splfileinfo.getbasename.php
        $basename = $object->getBasename();
        if ( '.' === $basename || '..' === $basename || $object->isDir() )
            continue;
        $zip->add_file_from_path( ltrim( $name, '/' ), $name );
        //DBG echo $name.'<br>';
        $added[] = $name;
    }

    $zip->finish();
    if ( function_exists( 'proc_nice' ) )
        proc_nice( 0 );

    return $added;
}

class ArchiveStream
{
	protected $use_container_dir = false;

	protected $container_dir_name = '';

	private $errors = array();

	private $error_log_filename = 'archive_errors.log';

	private $error_header_text = 'The following errors were encountered while generating this archive:';

	protected $block_size = 1048576; // process in 1 megabyte chunks
	/**
	 * Create a new ArchiveStream object.
	 *
	 * @param string $name name of output file (optional).
	 * @param array $opt hash of archive options (see archive options in readme)
	 * @access public
	 */
	public function __construct($name = null, $opt = array(), $base_path = null)
	{
		// save options
		$this->opt = $opt;

		// if a $base_path was passed set the protected property with that value, otherwise leave it empty
		$this->container_dir_name = isset($base_path) ? $base_path . '/' : '';

		// set large file defaults: size = 20 megabytes, method = store
		if (!isset($this->opt['large_file_size']))
		{
			$this->opt['large_file_size'] = 20 * 1024 * 1024;
		}

		if (!isset($this->opt['large_files_only']))
		{
			$this->opt['large_files_only'] = false;
		}

		$this->output_name = $name;
		if ($name || isset($opt['send_http_headers']))
		{
			$this->need_headers = true;
		}

		// turn off output buffering
		while (ob_get_level() > 0)
		{
			ob_end_flush();
		}
	}

	/**
	 * Create instance based on useragent string
	 *
	 * @param string $base_filename the base of the filename that will be appended with the correct extention
	 * @param array $opt hash of archive options (see above for list)
	 * @return ArchiveStream for either zip or tar
	 * @access public
	 */
	public static function instance_by_useragent($base_filename = null, $opt = array())
	{
		$user_agent = (isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '');

		// detect windows and use zip
		if (strpos($user_agent, 'windows') !== false)
		{
			require_once(__DIR__ . '/zipstream.php');
			$filename = (($base_filename === null) ? null : $base_filename . '.zip');
			return new ArchiveStream_Zip($filename, $opt, $base_filename);
		}
		// fallback to tar
		else
		{
			require_once(__DIR__ . '/tarstream.php');
			$filename = (($base_filename === null) ? null : $base_filename . '.tar');
			return new ArchiveStream_Tar($filename, $opt, $base_filename);
		}
	}

	/**
	 * Add file to the archive
	 *
	 * Parameters:
	 *
	 * @param string $name path of file in archive (including directory).
	 * @param string $data contents of file
     * @param array $opt hash of file options (see above for list)
     * @access public
	 */
	public function add_file($name, $data, $opt = array())
	{
		// calculate header attributes
		$this->meth_str = 'deflate';
		$meth = 0x08;

		// send file header
		$this->init_file_stream_transfer($name, strlen($data), $opt, $meth);

		// send data
		$this->stream_file_part($data, $single_part = true);

		// complete the file stream
		$this->complete_file_stream();
	}

	/**
	 * Add file by path
	 *
	 * @param string $name name of file in archive (including directory path).
	 * @param string $path path to file on disk (note: paths should be encoded using
	 *          UNIX-style forward slashes -- e.g '/path/to/some/file').
     * @param array $opt hash of file options (see above for list)
	 * @access public
	 */
	public function add_file_from_path($name, $path, $opt = array())
	{
		if ($this->opt['large_files_only'] || $this->is_large_file($path))
		{
			// file is too large to be read into memory; add progressively
			$this->add_large_file($name, $path, $opt);
		}
		else
		{
			// file is small enough to read into memory; read file contents and
			// handle with add_file()
			$data = file_get_contents($path);
			$this->add_file($name, $data, $opt);
		}
	}

	/**
	 * Log an error to be output at the end of the archive
	 *
	 * @param string $message error text to display in log file
	 */
	public function push_error($message)
	{
		$this->errors[] = (string) $message;
	}

	/**
	 * Set whether or not all elements in the archive will be placed within one container directory
	 *
	 * @param bool $bool true to use contaner directory, false to prevent using one. Defaults to false
	 */
	public function set_use_container_dir($bool = false)
	{
		$this->use_container_dir = (bool) $bool;
	}

	/**
	 * Set the name filename for the error log file when it's added to the archive
	 *
	 * @param string $name the filename for the error log
	 */
	public function set_error_log_filename($name)
	{
		if (isset($name))
		{
			$this->error_log_filename = (string) $name;
		}
	}

	/**
	 * Set the first line of text in the error log file
	 *
	 * @param string $msg the text to display on the first line of the error log file
	 */
	public function set_error_header_text($msg)
	{
		if (isset($msg))
		{
			$this->error_header_text = (string) $msg;
		}
	}

	/***************************
	 * PRIVATE UTILITY METHODS *
	 ***************************/

	/**
	 * Add a large file from the given path
	 *
	 * @param string $name name of file in archive (including directory path).
	 * @param string $path path to file on disk (note: paths should be encoded using
	 *          UNIX-style forward slashes -- e.g '/path/to/some/file').
     * @param array $opt hash of file options (see above for list)
     * @access protected
	 */
	protected function add_large_file($name, $path, $opt = array())
	{
		// send file header
		$this->init_file_stream_transfer($name, filesize($path), $opt);

		// open input file
		$fh = fopen($path, 'rb');

		// send file blocks
		while ($data = fread($fh, $this->block_size))
		{
			// send data
			$this->stream_file_part($data);
		}

		// close input file
		fclose($fh);

		// complete the file stream
		$this->complete_file_stream();
	}

	/**
	 * Is this file larger than large_file_size?
	 *
	 * @param string $path path to file on disk
	 * @return bool true if large, false if small
	 * @access protected
	 */
	protected function is_large_file($path)
	{
		$st = stat($path);
		return ($this->opt['large_file_size'] > 0) && ($st['size'] > $this->opt['large_file_size']);
	}

	/**
	 * Send HTTP headers for this stream.
	 *
	 * @access private
	 */
	private function send_http_headers()
	{
		// grab options
		$opt = $this->opt;

		// grab content type from options
		if ( isset($opt['content_type']) )
			$content_type = $opt['content_type'];
		else
			$content_type = 'application/x-zip';

		// grab content type encoding from options and append to the content type option
		if ( isset($opt['content_type_encoding']) )
			$content_type .= '; charset=' . $opt['content_type_encoding'];

		// grab content disposition
		$disposition = 'attachment';
		if ( isset($opt['content_disposition']) )
			$disposition = $opt['content_disposition'];

		if ( $this->output_name )
			$disposition .= "; filename=\"{$this->output_name}\"";

		$headers = array(
			'Content-Type'              => $content_type,
			'Content-Disposition'       => $disposition,
			'Pragma'                    => 'public',
			'Cache-Control'             => 'public, must-revalidate',
			'Content-Transfer-Encoding' => 'binary',
		);

		foreach ( $headers as $key => $val )
			header("$key: $val");
	}

	/**
	 * Send string, sending HTTP headers if necessary.
	 *
	 * @param string $data data to send
	 * @access protected
	 */
	protected function send( $data )
	{
		if ($this->need_headers)
			$this->send_http_headers();
		$this->need_headers = false;

		echo $data;
	}

	/**
	 * If errors were encountered, add an error log file to the end of the archive
	 */
	public function add_error_log()
	{
		if (!empty($this->errors))
		{
			$msg = $this->error_header_text;
			foreach ($this->errors as $err)
			{
				$msg .= "\r\n\r\n" . $err;
			}

			// stash current value so it can be reset later
			$temp = $this->use_container_dir;

			// set to false to put the error log file in the root instead of the container directory, if we're using one
			$this->use_container_dir = false;

			$this->add_file($this->error_log_filename, $msg);

			// reset to original value and dump the temp variable
			$this->use_container_dir = $temp;
			unset($temp);
		}
	}

	/**
	 * Convert a UNIX timestamp to a DOS timestamp.
	 *
	 * @param int $when unix timestamp
	 * @return string DOS timestamp
	 * @access protected
	 */
	protected function dostime( $when = 0 )
	{
		// get date array for timestamp
		$d = getdate($when);

		// set lower-bound on dates
		if ($d['year'] < 1980) {
			$d = array('year' => 1980, 'mon' => 1, 'mday' => 1,
				'hours' => 0, 'minutes' => 0, 'seconds' => 0);
		}

		// remove extra years from 1980
		$d['year'] -= 1980;

		// return date string
		return ($d['year'] << 25) | ($d['mon'] << 21) | ($d['mday'] << 16) |
				($d['hours'] << 11) | ($d['minutes'] << 5) | ($d['seconds'] >> 1);
	}

	/**
	 * Split a 64bit integer to two 32bit integers
	 *
	 * @param mixed $value integer or gmp resource
	 * @return array containing high and low 32bit integers
	 * @access protected
	 */
	protected function int64_split($value)
	{
		// gmp
		if (is_resource($value))
		{
			$hex  = str_pad(gmp_strval($value, 16), 16, '0', STR_PAD_LEFT);

	        $high = $this->gmp_convert(substr($hex, 0, 8), 16, 10);
	        $low  = $this->gmp_convert(substr($hex, 8, 8), 16, 10);
		}
		// int
		else
		{
			$left  = 0xffffffff00000000;
			$right = 0x00000000ffffffff;

			$high = ($value & $left) >>32;
			$low  = $value & $right;
		}

		return array($low, $high);
	}

	/**
	 * Create a format string and argument list for pack(), then call pack() and return the result.
	 *
	 * @param array key being the format string and value being the data to pack
	 * @return string binary packed data returned from pack()
	 * @access protected
	 */
	protected function pack_fields( $fields )
	{
		list ($fmt, $args) = array('', array());

		// populate format string and argument list
		foreach ($fields as $field) {
			$fmt .= $field[0];
			$args[] = $field[1];
		}

		// prepend format string to argument list
		array_unshift($args, $fmt);

		// build output string from header and compressed data
		return call_user_func_array('pack', $args);
	}

	/**
	 * Convert a number between bases via gmp
	 *
	 * @param int $num number to convert
	 * @param int $base_a base to convert from
	 * @param int $base_b base to convert to
	 * @return string number in string format
	 * @access private
	 */
	private function gmp_convert($num, $base_a, $base_b)
	{
		$gmp_num = gmp_init($num, $base_a);

		if (!$gmp_num)
		{
			die("gmp_convert could not convert [$num] from base [$base_a] to base [$base_b]");
		}

	    return gmp_strval ($gmp_num, $base_b);
	}
}

class ArchiveStream_Zip extends ArchiveStream
{
	// options array
	public $opt = array();

	// files tracked for cdr
	private $files = array();

	// length of the CDR
	private $cdr_len = 0;

	// offset of the CDR
	private $cdr_ofs = 0;

	// will hold both the uncompressed and compressed length
	private $len = null;
	private $zlen = null;

	// version zip created by / must be opened by (4.5 for zip64 support)
	const VERSION = 45;

	/**
	 * Create a new ArchiveStream_Zip object.
	 *
	 * @see ArchiveStream for documentation
	 * @access public
	 */
	public function __construct()
	{
		$this->opt['content_type'] = 'application/x-zip';
		call_user_func_array(array('parent', '__construct'), func_get_args());
	}

	/**
	 * Initialize a file stream
	 *
	 * @param string $name file path or just name
	 * @param int $size size in bytes of the file
	 * @param array $opt array containing time / type (optional)
	 * @param int $meth method of compression to use (defaults to store)
	 * @access public
	 */
	public function init_file_stream_transfer($name, $size, $opt = array(), $meth = 0x00)
	{
		// if we're using a container directory, prepend it to the filename
		if ($this->use_container_dir)
		{
			// the container directory will end with a '/' so ensure the filename doesn't start with one
			$name = $this->container_dir_name . preg_replace('/^\\/+/', '', $name);
		}

		$algo = 'crc32b';

		// calculate header attributes
		$this->len = gmp_init(0);
		$this->zlen = gmp_init(0);
		$this->hash_ctx = hash_init($algo);

		// Send file header
		$this->add_stream_file_header($name, $size, $opt, $meth);
	}

	/**
	 * Stream the next part of the current file stream.
	 *
	 * @param $data raw data to send
	 * @param bool $single_part used to determin if we can compress
	 * @access public
	 */
	public function stream_file_part($data, $single_part = false)
	{
		$this->len = gmp_add(gmp_init(strlen($data)), $this->len);
		hash_update($this->hash_ctx, $data);

		if ($single_part === true && isset($this->meth_str) && $this->meth_str == 'deflate')
		{
			$data = gzdeflate($data);
		}

		$this->zlen = gmp_add(gmp_init(strlen($data)), $this->zlen);

		// send data
		$this->send($data);
		flush();
	}

	/**
	 * Complete the current file stream (zip64 format).
	 *
	 * @access private
	 */
	public function complete_file_stream()
	{
		$crc = hexdec(hash_final($this->hash_ctx));

		// convert the 64 bit ints to 2 32bit ints
		list($zlen_low, $zlen_high) = $this->int64_split($this->zlen);
		list($len_low, $len_high) = $this->int64_split($this->len);

		// build data descriptor
		$fields = array(                // (from V.A of APPNOTE.TXT)
			array('V', 0x08074b50),     // data descriptor
			array('V', $crc),           // crc32 of data
			array('V', $zlen_low),      // compressed data length (low)
			array('V', $zlen_high),     // compressed data length (high)
			array('V', $len_low),       // uncompressed data length (low)
			array('V', $len_high),      // uncompressed data length (high)
		);

		// pack fields and calculate "total" length
		$ret = $this->pack_fields($fields);

		// print header and filename
		$this->send($ret);

		// Update cdr for file record
		$this->current_file_stream[3] = $crc;
		$this->current_file_stream[4] = gmp_strval($this->zlen);
		$this->current_file_stream[5] = gmp_strval($this->len);
		$this->current_file_stream[6] += gmp_strval( gmp_add( gmp_init(strlen($ret)), $this->zlen ) );
		ksort($this->current_file_stream);

		// Add to cdr and increment offset - can't call directly because we pass an array of params
		call_user_func_array(array($this, 'add_to_cdr'), $this->current_file_stream);
	}

	/**
	 * Finish an archive
	 *
	 * @access public
	 */
	public function finish()
	{
		// adds an error log file if we've been tracking errors
		$this->add_error_log();

		// add trailing cdr record
		$this->add_cdr($this->opt);
		$this->clear();
	}

	/*******************
	 * PRIVATE METHODS *
	 *******************/

	/**
	 * Add initial headers for file stream
	 *
	 * @param string $name file path or just name
	 * @param int $size size in bytes of the file
	 * @param array $opt array containing time
	 * @param int $meth method of compression to use
	 */
	protected function add_stream_file_header($name, $size, $opt, $meth)
	{
		// strip leading slashes from file name
		// (fixes bug in windows archive viewer)
		$name = preg_replace('/^\\/+/', '', $name);
		$extra = pack('vVVVV', 1, 0, 0, 0, 0);

		// create dos timestamp
		$opt['time'] = isset($opt['time']) ? $opt['time'] : time();
		$dts = $this->dostime($opt['time']);

		// Sets bit 3, which means CRC-32, uncompressed and compresed length
		// are put in the data descriptor following the data. This gives us time
		// to figure out the correct sizes, etc.
		$genb = 0x08;

		if (mb_check_encoding($name, "UTF-8") && !mb_check_encoding($name, "ASCII")) {
			// Sets Bit 11: Language encoding flag (EFS).  If this bit is set,
			// the filename and comment fields for this file
			// MUST be encoded using UTF-8. (see APPENDIX D)
			$genb |= 0x0800;
		}

		// build file header
		$fields = array(                // (from V.A of APPNOTE.TXT)
			array('V', 0x04034b50),     // local file header signature
			array('v', self::VERSION),  // version needed to extract
			array('v', $genb),          // general purpose bit flag
			array('v', $meth),          // compresion method (deflate or store)
			array('V', $dts),           // dos timestamp
			array('V', 0x00),           // crc32 of data (0x00 because bit 3 set in $genb)
			array('V', 0xFFFFFFFF),     // compressed data length
			array('V', 0xFFFFFFFF),     // uncompressed data length
			array('v', strlen($name)),  // filename length
			array('v', strlen($extra)), // extra data len
		);

		// pack fields and calculate "total" length
		$ret = $this->pack_fields($fields);

		// print header and filename
		$this->send($ret . $name . $extra);

		// Keep track of data for central directory record
		$this->current_file_stream = array(
			$name,
			$opt,
			$meth,
			// 3-5 will be filled in by complete_file_stream()
			6 => (strlen($ret) + strlen($name) + strlen($extra)),
			7 => $genb,
		);
	}

	/**
	 * Save file attributes for trailing CDR record
	 *
	 * @param string $name path / name of the file
	 * @param array $opt array containing time
	 * @param int $meth method of compression to use
	 * @param string $crc computed checksum of the file
	 * @param int $zlen compressed size
	 * @param int $len uncompressed size
	 * @param int $rec_size size of the record
	 * @param int $genb general purpose bit flag
	 * @access private
	 */
	private function add_to_cdr($name, $opt, $meth, $crc, $zlen, $len, $rec_len, $genb = 0)
	{
		$this->files[] = array($name, $opt, $meth, $crc, $zlen, $len, $this->cdr_ofs, $genb);
		$this->cdr_ofs += $rec_len;
	}

	/**
	 * Send CDR record for specified file (zip64 format).
	 *
	 * @param array $args array of args
	 * @see add_to_cdr() for details of the args
	 * @access private
	 */
	private function add_cdr_file($args)
	{
		list($name, $opt, $meth, $crc, $zlen, $len, $ofs, $genb) = $args;

		// convert the 64 bit ints to 2 32bit ints
		list($zlen_low, $zlen_high) = $this->int64_split($zlen);
		list($len_low, $len_high)   = $this->int64_split($len);
		list($ofs_low, $ofs_high)   = $this->int64_split($ofs);

		// ZIP64, necessary for files over 4GB (incl. entire archive size)
		$extra_zip64 = '';
		$extra_zip64 .= pack('VV', $len_low, $len_high);
		$extra_zip64 .= pack('VV', $zlen_low, $zlen_high);
		$extra_zip64 .= pack('VV', $ofs_low, $ofs_high);

		$extra = pack('vv', 1, strlen($extra_zip64)) . $extra_zip64;

		// get attributes
		$comment = isset($opt['comment']) ? $opt['comment'] : '';

		// get dos timestamp
		$dts = $this->dostime($opt['time']);

		$fields = array(                      // (from V,F of APPNOTE.TXT)
			array('V', 0x02014b50),           // central file header signature
			array('v', self::VERSION),        // version made by
			array('v', self::VERSION),        // version needed to extract
			array('v', $genb),                // general purpose bit flag
			array('v', $meth),                // compresion method (deflate or store)
			array('V', $dts),                 // dos timestamp
			array('V', $crc),                 // crc32 of data
			array('V', 0xFFFFFFFF),           // compressed data length (zip64 - look in extra)
			array('V', 0xFFFFFFFF),           // uncompressed data length (zip64 - look in extra)
			array('v', strlen($name)),        // filename length
			array('v', strlen($extra)),       // extra data len
			array('v', strlen($comment)),     // file comment length
			array('v', 0),                    // disk number start
			array('v', 0),                    // internal file attributes
			array('V', 32),                   // external file attributes
			array('V', 0xFFFFFFFF),           // relative offset of local header (zip64 - look in extra)
		);

		// pack fields, then append name and comment
		$ret = $this->pack_fields($fields) . $name . $extra . $comment;

		$this->send($ret);

		// increment cdr length
		$this->cdr_len += strlen($ret);
	}

	/**
	 * Adds Zip64 end of central directory record
	 *
	 * @param int $cdr_start the offset where the cdr starts
	 * @access private
	 */
	private function add_cdr_eof_zip64()
	{
		$num = count($this->files);

		list($num_low, $num_high) = $this->int64_split($num);
		list($cdr_len_low, $cdr_len_high) = $this->int64_split($this->cdr_len);
		list($cdr_ofs_low, $cdr_ofs_high) = $this->int64_split($this->cdr_ofs);

		$fields = array(                    // (from V,F of APPNOTE.TXT)
			array('V', 0x06064b50),         // zip64 end of central directory signature
			array('V', 44),                 // size of zip64 end of central directory record (low) minus 12 bytes
			array('V', 0),                  // size of zip64 end of central directory record (high)
			array('v', self::VERSION),      // version made by
			array('v', self::VERSION),      // version needed to extract
			array('V', 0x0000),             // this disk number (only one disk)
			array('V', 0x0000),             // number of disk with central dir
			array('V', $num_low),           // number of entries in the cdr for this disk (low)
			array('V', $num_high),          // number of entries in the cdr for this disk (high)
			array('V', $num_low),           // number of entries in the cdr (low)
			array('V', $num_high),          // number of entries in the cdr (high)
			array('V', $cdr_len_low),       // cdr size (low)
			array('V', $cdr_len_high),      // cdr size (high)
			array('V', $cdr_ofs_low),       // cdr ofs (low)
			array('V', $cdr_ofs_high),      // cdr ofs (high)
		);

		$ret = $this->pack_fields($fields);
		$this->send($ret);
	}

	/**
	 * Add location record for ZIP64 central directory
	 *
	 * @access private
	 */
	private function add_cdr_eof_locator_zip64()
	{
		list($cdr_ofs_low, $cdr_ofs_high) = $this->int64_split($this->cdr_len + $this->cdr_ofs);

		$fields = array(                    // (from V,F of APPNOTE.TXT)
			array('V', 0x07064b50),         // zip64 end of central dir locator signature
			array('V', 0),                  // this disk number
			array('V', $cdr_ofs_low),       // cdr ofs (low)
			array('V', $cdr_ofs_high),      // cdr ofs (high)
			array('V', 1),                  // total number of disks
		);

		$ret = $this->pack_fields($fields);
		$this->send($ret);
	}

	/**
	 * Send CDR EOF (Central Directory Record End-of-File) record. Most values
	 * point to the corresponding values in the ZIP64 CDR. The optional comment
	 * still goes in this CDR however.
	 *
	 * @param array $opt options array that may contain a comment
	 * @access private
	 */
	private function add_cdr_eof($opt = null)
	{
		// grab comment (if specified)
		$comment = '';
		if ($opt && isset($opt['comment']))
		{
			$comment = $opt['comment'];
		}

		$fields = array(                    // (from V,F of APPNOTE.TXT)
			array('V', 0x06054b50),         // end of central file header signature
			array('v', 0xFFFF),             // this disk number (0xFFFF to look in zip64 cdr)
			array('v', 0xFFFF),             // number of disk with cdr (0xFFFF to look in zip64 cdr)
			array('v', 0xFFFF),             // number of entries in the cdr on this disk (0xFFFF to look in zip64 cdr))
			array('v', 0xFFFF),             // number of entries in the cdr (0xFFFF to look in zip64 cdr)
			array('V', 0xFFFFFFFF),         // cdr size (0xFFFFFFFF to look in zip64 cdr)
			array('V', 0xFFFFFFFF),         // cdr offset (0xFFFFFFFF to look in zip64 cdr)
			array('v', strlen($comment)),   // zip file comment length
		);

		$ret = $this->pack_fields($fields) . $comment;
		$this->send($ret);
	}

	/**
	 * Add CDR (Central Directory Record) footer.
	 *
     * @param array $opt options array that may contain a comment
	 * @access private
	 */
	private function add_cdr($opt = null)
	{
		foreach ($this->files as $file)
		{
			$this->add_cdr_file($file);
		}

		$this->add_cdr_eof_zip64();
		$this->add_cdr_eof_locator_zip64();

		$this->add_cdr_eof($opt);
	}

	/**
	 * Clear all internal variables.
	 *
	 * Note: that the stream object is not usable after this.
	 *
	 * @access private
	 */
	private function clear()
	{
		$this->files = array();
		$this->cdr_ofs = 0;
		$this->cdr_len = 0;
		$this->opt = array();
	}
}
