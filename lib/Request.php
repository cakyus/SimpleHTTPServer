<?php

namespace SimpleHTTPServer;

class Request {
	
	// <CONFIGURATION>
		// Maximum number of bytes which client can send
	private $maxRequestSize;
		// MIME Content Type
	private $mimeContentType;
	// </CONFIGURATION>
	
	private $CGIVars;
	
	// all string remote user sent to server
	private $contentRaw;
	// all header which is part of $contentRaw
	private $headerRaw;
	// indicate whether user send a valid HTTP request
	private $valid;
	// all post message which is part of $contentRaw
	private $postMessage;
	
	public function __construct($sock, Server $server) {
		
		$config = new Config;
		$logger = new Logger;
		
		$this->CGIVars = array();
		$this->headers = array();
		$this->valid = false;
		
		// $_ENV['DOCUMENT_ROOT']
		$this->CGIVars['DOCUMENT_ROOT'] = $config->documentRoot;
		$this->maxRequestSize = $config->maxRequestSize;
		$this->mimeContentType = $config->mimeContentType;
		
		// $_ENV['REMOTE_ADDR']
		// $_ENV['REMOTE_PORT']
		socket_getpeername(
			  $sock
			, $this->CGIVars['REMOTE_ADDR']
			, $this->CGIVars['REMOTE_PORT']
			);
			
        if (false === ($this->contentRaw = socket_read($sock, $this->maxRequestSize))) {
            $logger->write('socket_read() failed'
				, socket_strerror(socket_last_error($sock))
				);
			return false;
        }
        
        if (!$this->contentRaw = trim($this->contentRaw)) {
			return false;
        }
        
        // $_ENV['REQUEST_METHOD']
        // $_ENV['REQUEST_URI']
        if (preg_match("/^([A-Z]+) ([^\s]+) HTTP\/[0-9]\.[0-9]\r\n(.+?)(\r\n\r\n(.*))?$/s"
			, $this->contentRaw, $match)) {
			$this->CGIVars['REQUEST_METHOD'] = $match[1];
			$this->CGIVars['REQUEST_URI'] = $match[2];
			$this->headerRaw = $match[3];
			$this->valid = true;
			if (isset($match[5])) {
				$this->postMessage = $match[5];
			}
			
			// $_ENV['SCRIPT_NAME']
			// $_ENV['SCRIPT_FILENAME']
			preg_match("/^[^\?]+/s", $this->CGIVars['REQUEST_URI'], $match);
			$this->CGIVars['SCRIPT_NAME'] = $match[0];
			
			$this->CGIVars['SCRIPT_FILENAME'] = $this->CGIVars['DOCUMENT_ROOT']
				.$this->CGIVars['SCRIPT_NAME']
				;

			// $_ENV['QUERY_STRING']
			if (preg_match("/\?([^\s]+)/", $this->CGIVars['REQUEST_URI'], $match)) {
				$this->CGIVars['QUERY_STRING'] = $match[1];
			}
			
			// $_ENV['HTTP_*']
			if (preg_match_all("/^([^:]+): (.+)\s$/m", $this->headerRaw, $match)) {
				for ($i = 0; $i < count($match[1]); $i++){
					// eg. User-Agent -> HTTP_USER_AGENT
					$this->CGIVars['HTTP_'.strtoupper(str_replace('-','_',$match[1][$i]))]
						= $match[2][$i];
				}
			}
			
			// POST METHOD
			// $_ENV['CONTENT_LENGTH']
			// $_ENV['CONTENT_TYPE']
			if ($this->CGIVars['REQUEST_METHOD'] == 'POST') {
				$this->CGIVars['CONTENT_LENGTH'] = strlen($this->postMessage);
				if (isset($this->CGIVars['HTTP_CONTENT_TYPE'])) {
					$this->CGIVars['CONTENT_TYPE'] = $this->CGIVars['HTTP_CONTENT_TYPE'];
				}
			}
		}
	}
	
	public function getPostMessage() {
		return $this->postMessage;
	}
	
	public function isValid() {
		return $this->valid;
	}
	
	public function getHeaderRaw() {
		return $this->headerRaw;
	}
	
	public function getContentRaw() {
		return $this->contentRaw;
	}
	

	public function getCGIVars() {
		return $this->CGIVars;
	}
	
	/**
	 * Set CGIVar value
	 * @return boolean
	 **/
	public function setCGIVar($name, $value) {
		$this->CGIVars[$name] = $value;
		return true;
	}
	
	public function getCGIVar($name) {
		if (isset($this->CGIVars[$name])) {
			return $this->CGIVars[$name];
		}
		return null;
	}
	
	public function getMimeContentType() {
		$mimeContentType = array(
			 'ai' => 'application/postscript'
			,'anx' => 'application/annodex'
			,'axa' => 'audio/annodex'
			,'axv' => 'video/annodex'
			,'bcpio' => 'application/x-bcpio'
			,'bin' => 'application/octet-stream'
			,'ccad' => 'application/clariscad'
			,'cdf' => 'application/x-netcdf'
			,'class' => 'application/octet-stream'
			,'cpio' => 'application/x-cpio'
			,'cpt' => 'application/mac-compactpro'
			,'csh' => 'application/x-csh'
			,'dcr' => 'application/x-director'
			,'dir' => 'application/x-director'
			,'dms' => 'application/octet-stream'
			,'doc' => 'application/msword'
			,'drw' => 'application/drafting'
			,'dvi' => 'application/x-dvi'
			,'dwg' => 'application/acad'
			,'dxf' => 'application/dxf'
			,'dxr' => 'application/x-director'
			,'flac' => 'audio/flac'
			,'eps' => 'application/postscript'
			,'exe' => 'application/octet-stream'
			,'ez' => 'application/andrew-inset'
			,'gtar' => 'application/x-gtar'
			,'gz' => 'application/x-gzip'
			,'hdf' => 'application/x-hdf'
			,'hqx' => 'application/mac-binhex40'
			,'ips' => 'application/x-ipscript'
			,'ipx' => 'application/x-ipix'
			,'iso' => 'application/x-isoview;'
			,'js' => 'application/x-javascript'
			,'latex' => 'application/x-latex'
			,'lha' => 'application/octet-stream'
			,'lnk' => 'application/x-ms-shortcut'
			,'lsp' => 'application/x-lisp'
			,'lzh' => 'application/octet-stream'
			,'man' => 'application/x-troff-man'
			,'me' => 'application/x-troff-me'
			,'mif' => 'application/vnd.mif'
			,'ms' => 'application/x-troff-ms'
			,'nc' => 'application/x-netcdf'
			,'oda' => 'application/oda'
			,'odt' => 'application/vnd.oasis.opendocument.text'
			,'ods' => 'application/vnd.oasis.opendocument.spreadsheet'
			,'odp' => 'application/vnd.oasis.opendocument.presentation'
			,'odg' => 'application/vnd.oasis.opendocument.graphics'
			,'odc' => 'application/vnd.oasis.opendocument.chart'
			,'odf' => 'application/vnd.oasis.opendocument.formula'
			,'odb' => 'application/vnd.oasis.opendocument.database'
			,'odi' => 'application/vnd.oasis.opendocument.image'
			,'odm' => 'application/vnd.oasis.opendocument.text-master'
			,'oga' => 'audio/ogg'
			,'ogg' => 'audio/ogg'
			,'ogv' => 'video/ogg'
			,'ogx' => 'application/ogg'
			,'ott' => 'application/vnd.oasis.opendocument.text-template'
			,'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template'
			,'otp' => 'application/vnd.oasis.opendocument.presentation-template'
			,'otg' => 'application/vnd.oasis.opendocument.graphics-template'
			,'pdf' => 'application/pdf'
			,'pgn' => 'application/x-chess-pgn'
			,'pot' => 'application/mspowerpoint'
			,'pps' => 'application/mspowerpoint'
			,'ppt' => 'application/mspowerpoint'
			,'ppz' => 'application/mspowerpoint'
			,'pre' => 'application/x-freelance'
			,'prt' => 'application/pro_eng'
			,'ps' => 'application/postscript'
			,'rar' => 'application/x-rar'
			,'roff' => 'application/x-troff'
			,'scm' => 'application/x-lotusscreencam'
			,'set' => 'application/set'
			,'sh' => 'application/x-sh'
			,'shar' => 'application/x-shar'
			,'sit' => 'application/x-stuffit'
			,'skd' => 'application/x-koan'
			,'skm' => 'application/x-koan'
			,'skp' => 'application/x-koan'
			,'skt' => 'application/x-koan'
			,'smi' => 'application/smil'
			,'smil' => 'application/smil'
			,'sol' => 'application/solids'
			,'spl' => 'application/x-futuresplash'
			,'spx' => 'audio/ogg'
			,'src' => 'application/x-wais-source'
			,'step' => 'application/STEP'
			,'stl' => 'application/SLA'
			,'stp' => 'application/STEP'
			,'sv4cpio' => 'application/x-sv4cpio'
			,'sv4crc' => 'application/x-sv4crc'
			,'swf' => 'application/x-shockwave-flash'
			,'t' => 'application/x-troff'
			,'tar' => 'application/x-tar'
			,'tcl' => 'application/x-tcl'
			,'tex' => 'application/x-tex'
			,'texi' => 'application/x-texinfo'
			,'texinfo' => 'application/x-texinfo'
			,'tr' => 'application/x-troff'
			,'tsp' => 'application/dsptype'
			,'unv' => 'application/i-deas'
			,'ustar' => 'application/x-ustar'
			,'vcd' => 'application/x-cdlink'
			,'vda' => 'application/vda'
			,'xlc' => 'application/vnd.ms-excel'
			,'xll' => 'application/vnd.ms-excel'
			,'xlm' => 'application/vnd.ms-excel'
			,'xls' => 'application/vnd.ms-excel'
			,'xlw' => 'application/vnd.ms-excel'
			,'xpsf' => 'application/xpsf+xml'
			,'zip' => 'application/zip'
			,'aif' => 'audio/x-aiff'
			,'aifc' => 'audio/x-aiff'
			,'aiff' => 'audio/x-aiff'
			,'au' => 'audio/basic'
			,'kar' => 'audio/midi'
			,'mid' => 'audio/midi'
			,'midi' => 'audio/midi'
			,'mp2' => 'audio/mpeg'
			,'mp3' => 'audio/mpeg'
			,'mpga' => 'audio/mpeg'
			,'ra' => 'audio/x-realaudio'
			,'ram' => 'audio/x-pn-realaudio'
			,'rm' => 'audio/x-pn-realaudio'
			,'rpm' => 'audio/x-pn-realaudio-plugin'
			,'snd' => 'audio/basic'
			,'tsi' => 'audio/TSP-audio'
			,'wav' => 'audio/x-wav'
			,'as' => 'text/x-actionscript'
			,'asc' => 'text/plain'
			,'c' => 'text/plain'
			,'cc' => 'text/plain'
			,'css' => 'text/css'
			,'etx' => 'text/x-setext'
			,'f' => 'text/plain'
			,'f90' => 'text/plain'
			,'h' => 'text/plain'
			,'hh' => 'text/plain'
			,'htm' => 'text/html'
			,'html' => 'text/html'
			,'m' => 'text/plain'
			,'rtf' => 'text/rtf'
			,'rtx' => 'text/richtext'
			,'sgm' => 'text/sgml'
			,'sgml' => 'text/sgml'
			,'tsv' => 'text/tab-separated-values'
			,'tpl' => 'text/template'
			,'txt' => 'text/plain'
			,'xml' => 'text/xml'
			,'avi' => 'video/x-msvideo'
			,'fli' => 'video/x-fli'
			,'flv' => 'video/x-flv'
			,'mov' => 'video/quicktime'
			,'movie' => 'video/x-sgi-movie'
			,'mpe' => 'video/mpeg'
			,'mpeg' => 'video/mpeg'
			,'mpg' => 'video/mpeg'
			,'qt' => 'video/quicktime'
			,'viv' => 'video/vnd.vivo'
			,'vivo' => 'video/vnd.vivo'
			,'wmv' => 'video/x-ms-wmv'
			,'bmp' => 'image/bmp'
			,'gif' => 'image/gif'
			,'ief' => 'image/ief'
			,'jpe' => 'image/jpeg'
			,'jpeg' => 'image/jpeg'
			,'jpg' => 'image/jpeg'
			,'pbm' => 'image/x-portable-bitmap'
			,'pgm' => 'image/x-portable-graymap'
			,'png' => 'image/png'
			,'pnm' => 'image/x-portable-anymap'
			,'ppm' => 'image/x-portable-pixmap'
			,'psd' => 'image/psd'
			,'ras' => 'image/cmu-raster'
			,'rgb' => 'image/x-rgb'
			,'tif' => 'image/tiff'
			,'tiff' => 'image/tiff'
			,'xbm' => 'image/x-xbitmap'
			,'xpm' => 'image/x-xpixmap'
			,'xwd' => 'image/x-xwindowdump'
			,'ice' => 'x-conference/x-cooltalk'
			,'iges' => 'model/iges'
			,'igs' => 'model/iges'
			,'mesh' => 'model/mesh'
			,'msh' => 'model/mesh'
			,'silo' => 'model/mesh'
			,'vrml' => 'model/vrml'
			,'wrl' => 'model/vrml'
			,'mime' => 'www/mime'
			,'pdb' => 'chemical/x-pdb'
			,'xyz' => 'chemical/x-pdb'
			);
			
			if (preg_match("/[^\.]+$/", $this->CGIVars['SCRIPT_FILENAME'], $match)) {
				if (isset($mimeContentType[$match[0]])) {
					return $mimeContentType[$match[0]]; 
				}
			}
			
			return $this->mimeContentType;
	}
	
}
