<?php

namespace SimpleHTTPServer;

class Request {
	
	// <CONFIGURATION>
	private $maxRequestSize = 2097152; // 2MB
	private $documentRoot;
	private $mimeContentType = 'text/html';	
	// </CONFIGURATION>
	
	private $remoteHost;
	private $remotePort;
	private $method;
	private $headerRaw;
	private $uri;
	private $content;
	private $contentRaw;
	private $valid;
	private $postMessage;
	private $uriPath;
	private $scriptPath;
	
	public function __construct($sock) {
		
		$logger = new Logger;
		
		$this->headers = array();
		$this->valid = false;
		$this->documentRoot = dirname(dirname(__FILE__)).'/htdocs';
		
		socket_getpeername(
			  $sock
			, $this->remoteHost
			, $this->remotePort
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
        
        // @todo POST Request will requires advanced regex pattern
        //       for request body
        if (preg_match("/^(GET|POST) ([^\s]+) HTTP\/[0-9]\.[0-9]\r\n(.+?)(\r\n\r\n(.*))?$/s"
			, $this->contentRaw, $match)) {
			$this->method = $match[1];
			$this->uri = $match[2];
			$this->headerRaw = $match[3];
			$this->valid = true;
			if (isset($match[5])) {
				$this->postMessage = $match[5];
			}
			
			preg_match("/^[^\?]+/s", $this->uri, $match);
			$this->uriPath = $match[0];
			
			$this->scriptPath = $this->documentRoot.$this->uriPath;

			// resolve directory index files
			if (is_dir($this->scriptPath)) {
				if (is_file($this->scriptPath.'index.html')) {
					$this->scriptPath .= 'index.html';
					$this->uriPath .= 'index.html';
				} elseif (is_file($this->scriptPath.'index.php')) {
					$this->scriptPath .= 'index.php';
					$this->uriPath .= 'index.php';
				}
			} elseif (!is_file($this->scriptPath)) {
				$this->scriptPath = false;
			}
		}
		
	}
	
	public function getPostMessage() {
		return $this->postMessage;
	}
	
	public function isValid() {
		return $this->valid;
	}
	
	public function getMethod() {
		return $this->method;
	}
	
	public function getPath() {
		return $this->uriPath;
	}
	
	public function getQueryString() {
		if (preg_match("/\?([^\s]+)/", $this->uri, $match)) {
			return $match[1];
		}
		return false;
	}
	
	public function getURI() {
		return $this->uri;
	}
	
	public function getHeaderRaw() {
		return $this->headerRaw;
	}
	
	public function getContent() {
		return $this->content;
	}
	
	public function getContentRaw() {
		return $this->contentRaw;
	}
	
	public function getDocumentRoot() {
		return $this->documentRoot;
	}
	
	public function getScriptPath() {
		return $this->scriptPath;
	}
	
	public function getCGIVars() {
		
		$vars = array();
		
		// Minimum CGI Environments Variables
		$vars['REQUEST_METHOD'] = $this->getMethod();
		$vars['QUERY_STRING'] = $this->getQueryString();
		$vars['REQUEST_URI'] = $this->getURI();
		$vars['SCRIPT_NAME'] = $this->getPath();
		$vars['REMOTE_ADDR'] = $this->getRemoteHost();
		$vars['REMOTE_PORT'] = $this->getRemotePort();
		$vars['SCRIPT_FILENAME'] = $this->getScriptPath();
		$vars['DOCUMENT_ROOT'] = $this->getDocumentRoot();
		$vars['REDIRECT_STATUS'] = 200;
		$vars['GATEWAY_INTERFACE'] = 'CGI/1.1';
		
		// Passthrough client HTTP Headers
		if (preg_match_all("/^([^:]+): (.+)\s$/m", $this->headerRaw, $match)) {
			for ($i = 0; $i < count($match[1]); $i++){
				// eg. User-Agent -> HTTP_USER_AGENT
				$vars['HTTP_'.strtoupper(str_replace('-','_',$match[1][$i]))]
					= $match[2][$i];
			}
		}
		
		// POST Method
		if ($this->getMethod() == 'POST') {
			$vars['CONTENT_LENGTH'] = strlen($this->getPostMessage());
			if (isset($vars['HTTP_CONTENT_TYPE'])) {
				$vars['CONTENT_TYPE'] = $vars['HTTP_CONTENT_TYPE'];
			}
		}
		
		return $vars;
	}
	
	public function getRemoteHost() {
		return $this->remoteHost;
	}
	
	public function getRemotePort() {
		return $this->remotePort;
	}
	
	public function getMimeContentType() {
		$mimeContentType = array(
			 'ai' => 'application/postscript'
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
			
			if (preg_match("/[^\.]+$/", $this->getScriptPath(), $match)) {
				if (isset($mimeContentType[$match[0]])) {
					return $mimeContentType[$match[0]]; 
				}
			}
			return $this->mimeContentType;
	}
	
}
