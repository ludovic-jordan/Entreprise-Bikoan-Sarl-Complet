<?php
require_once __DIR__.'/../vendor/autoload.php'; use App\Controllers\AuthController;
header('Access-Control-Allow-Origin: http://localhost:5173'); header('Access-Control-Allow-Credentials: true'); header('Content-Type: application/json; charset=UTF-8'); header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); header('Access-Control-Allow-Headers: Content-Type');
if($_SERVER['REQUEST_METHOD']==='OPTIONS'){http_response_code(204);exit;}$action=$_GET['action']??'health';
if($_SERVER['REQUEST_METHOD']==='POST'){try{(new AuthController())->handle($action);}catch(Throwable $e){http_response_code(500);echo json_encode(['status'=>'error','message'=>'Database error. Check that MySQL and the chip database are running.']);}exit;}echo json_encode(['status'=>'success','message'=>'Bikoan API is online.']);
