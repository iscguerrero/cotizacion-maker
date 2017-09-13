<?php 
	if (!defined('BASEPATH')) exit('No direct script access allowed');

	require_once APPPATH."/third_party/excel/PHPExcel/IOFactory.php";

	class ExcelReader extends PHPExcel_IOFactory {
		/*public function __construct() {
			parent::__construct();
		}*/
	}