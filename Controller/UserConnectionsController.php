<?php
/*
 * Copyright 2014 the original author or authors.
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
*      http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/
App::uses('ConnectionFactory', 'Social.Model');

/**
 * UserConnections Controller
 * 
 * @property UserConnection $UserConnection
 * @property Opauth $Opauth
 */
class UserConnectionsController extends SocialAppController {
	
	public $components = array(
		'Social.Connection'
		);
	
	public $Opauth;
	
	public function beforeFilter() {
		// Allow access to Opauth methods for users of AuthComponent
		if (is_object($this->Auth) && method_exists($this->Auth, 'allow')) {
			$this->Auth->allow();
		}
	
		//Disable Security for the plugin actions in case that Security Component is active
		if (is_object($this->Security)) {
			$this->Security->validatePost = false;
			$this->Security->csrfCheck = false;
		}
	}
	
	public function auth($provider) {
		$this->Connection->connect();
	}
	
	/**
	 * Receives auth response and does validation
	 */
	public function callback(){
		$response = null;
	
		/**
	     * Fetch auth response, based on transport configuration for callback
	     */
		switch(Configure::read('Opauth.callback_transport')){
			case 'session':
				if (!session_id()){
					session_start();
				}
	
				if(isset($_SESSION['opauth'])) {
					$response = $_SESSION['opauth'];
					unset($_SESSION['opauth']);
				}
				break;
			case 'post':
				$response = unserialize(base64_decode( $_POST['opauth'] ));
				break;
			case 'get':
				$response = unserialize(base64_decode( $_GET['opauth'] ));
				break;
			default:
				echo '<strong style="color: red;">Error: </strong>Unsupported callback_transport.'."<br>\n";
				break;
		}
	
		/**
		 * Check if it's an error callback
		 */
		if (isset($response) && is_array($response) && array_key_exists('error', $response)) {
			// Error
			$response['validated'] = false;
		}
	
		/**
		 * Auth response validation
		 *
		 * To validate that the auth response received is unaltered, especially auth response that
		 * is sent through GET or POST.
		 */
		else{
			if (empty($response['auth']) || empty($response['timestamp']) || empty($response['signature']) || empty($response['auth']['provider']) || empty($response['auth']['uid'])){
				$response['error'] = array(
					'provider' => $response['auth']['provider'],
					'code' => 'invalid_auth_missing_components',
					'message' => 'Invalid auth response: Missing key auth response components.'
				);
				$response['validated'] = false;
			} elseif (!($this->Connection->Opauth->validate(sha1(print_r($response['auth'], true)), $response['timestamp'], $response['signature'], $reason))){
				$response['error'] = array(
					'provider' => $response['auth']['provider'],
					'code' => 'invalid_auth_failed_validation',
					'message' => 'Invalid auth response: '.$reason
				);
				$response['validated'] = false;
			} else{
				$response['validated'] = true;
			}
		}
	
		$user = $this->Auth->user();
		if (!empty($user)) {
			$connection = $this->createConnection($response);
			$connection['user_id'] = $user['id'];
			$this->UserConnection->create();
			$this->UserConnection->save($connection);
			$this->Connection->handlePostSignIn();
			$this->redirect($this->referer());	// TODO
		}
		
		Debugger::log($response);
		
		$options = array('recursive' => 0, 'conditions' => array(
			'UserConnection.provider_id' => $response['auth']['provider'],
			'UserConnection.provider_user_id' => $response['auth']['raw']['id']
		));
		$connection = $this->UserConnection->find('first', $options);
		if (!$connection) {
			$connection = $this->createConnection($response);
			$this->Session->write('Connection.data', $connection);
			return $this->redirect(array('plugin' => false, 'controller' => 'accounts', 'action' => 'add'));	// TODO
		}
		
		$this->login($connection['User']);
	}
	
	protected function login($user) {
		if ($this->Auth->login($user)) {
			$this->Connection->handlePostSignIn();
			$this->redirect($this->Auth->redirectUrl());
		}
	}
	
	private function createConnection($response) {
		$provider = $response['auth']['provider'];
		App::uses($provider . 'ConnectionFactory', 'Social' . $provider . '.Lib');
		$class = new ReflectionClass($provider . 'ConnectionFactory');
		$connectionFactory = $class->newInstance();		
		return $connectionFactory->createConnection($response);
	}
}
