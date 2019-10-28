<?php
// No permitimos acceso directo
defined('_JEXEC') or die;
// Incluir el helper
require_once dirname(__FILE__) . '/helper.php';


echo "Esto es una prueba";
// Invocar las funciones para recibir los datos
$saludo = modHolaMundoHelper::getSaludo();
// Llamar a la plantilla del modulo
require JModuleHelper::getLayoutPath('mod_holamundo');