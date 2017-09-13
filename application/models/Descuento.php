<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Descuento extends CI_Model{
	public function __construct(){
		parent::__construct();
	}
	public function ObtenerDescuentoMaximo(){
		return array('descuento' => 15);
	}
}