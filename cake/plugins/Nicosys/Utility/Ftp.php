<?php

/**
 * FTP 関数のラッパ
 */
class Ftp {

	protected $handle = null;

	public function __construct($host, $port = 21, $timeout = 90) {
		$this->handle = ftp_connect($host, $port, $timeout);
		if ($this->handle === false) {
			throw new FtpException();
		}
	}

	public function __destruct() {
		if ($this->handle) {
			ftp_close($this->handle);
		}
	}

	public function login($username, $password) {
		if (!@ftp_login($this->handle, $username, $password)) {
			$errors = error_get_last();
			$message = $errors['message'];
			throw new FtpException($message);
		}
	}

	public function pasv($pasv) {
		if (!ftp_pasv($this->handle, $pasv)) {
			throw new FtpException();
		}
	}

	public function pwd() {
		$name = ftp_pwd($this->handle);
		if ($name === false) {
			throw new FtpException();
		}
		return $name;
	}

	public function chdir($directory) {
		if (!@ftp_chdir($this->handle, $directory)) {
			$errors = error_get_last();
			$message = $errors['message'];
			throw new FtpException($message);
		}
	}

	public function mkdir($directory) {
		$name = ftp_mkdir($this->handle, $directory);
		if ($name === false) {
			throw new FtpException();
		}
		return $name;
	}

	public function rmdir($directory) {
		if (!ftp_rmdir($this->handle, $directory)) {
			throw new FtpException();
		}
	}


	public function get($local_file, $remote_file, $mode, $resumepos = 0) {
		if (!ftp_get($this->handle, $local_file, $remote_file, $mode, $resumepos)) {
			throw new FtpException();
		}
	}

	public function put($remote_file, $local_file, $mode, $resumepos = 0) {
		if (!ftp_put($this->handle, $remote_file, $local_file, $mode, $resumepos )) {
			throw new FtpException();
		}
	}


	public function fget($handle, $remote_file, $mode, $resumepos = 0) {
		if (!ftp_fget($this->handle, $handle, $remote_file, $mode, $resumepos)) {
			throw new FtpException();
		}
	}

	public function fput($remote_file, $handle, $mode, $resumepos = 0) {
		if (!ftp_fput($this->handle, $remote_file, $handle, $mode, $resumepos)) {
			throw new FtpException();
		}
	}


	public function delete($path) {
		if (!ftp_delete($this->handle, $path)) {
			throw new FtpException();
		}
	}

}

class FtpException extends Exception {
	//
}
