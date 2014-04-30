<?php

/**
 * Connection Component
 *
 * @property UserConnection $UserConnection
 */
class ConnectionComponent extends Component {
	
	public $components = array(
		'Auth',
		'Session'
		);
	
	public $Opauth;
	
	private $UserConnection;
	
	public function __construct(ComponentCollection $collection, array $settings = array()) {
		parent::__construct($collection, $settings);
		$this->Controller = $collection->getController();
		$this->UserConnection = ClassRegistry::init('Social.UserConnection');
		
		$this->_loadOpauth();
	}
	
	public function connect($provider = null) {
		$this->Opauth->run();
	}
	
	public function isConnected($provider) {
		$userId = $this->Auth->user('id');
		$options = array('conditions' => array(
			'UserConnection.user_id' => $userId,
			'UserConnection.provider_id' => $provider
		));
		return (bool) $this->UserConnection->find('count', $options);
	}
	
	public function handlePostSignUp($userId) {
		$connection = $this->Session->read('Connection.data');
		$connection['user_id'] = $userId;
	
		$this->Session->delete('Connection.data');
	
		$this->UserConnection->create();
		return $this->UserConnection->save($connection);
	}
	
	public function handlePostSignIn() {
		$userId = $this->Controller->Auth->user('id');
	
		$options = array('conditions' => array(
			'UserConnection.user_id' => $userId
		));
		$connections = $this->UserConnection->find('all', $options);
		
		foreach ($connections as $connection) {
			$this->Controller->Session->write('Auth.' . $connection['UserConnection']['provider_id'], $connection['UserConnection']);
		}
	}
	
	public function delete($provider) {
		$userId = $this->Auth->user('id');
		$options = array('conditions' => array(
			'UserConnection.provider_id' => $provider,
			'UserConnection.user_id' => $userId
		));
		$connection = $this->UserConnection->find('first', $options);
		if (!empty($connection)) {
			$this->UserConnection->delete($connection['UserConnection']['id']);
			$this->Controller->Session->delete('Auth.' . $provider);
		}
	}
	
	/**
	 * Instantiate Opauth
	 *
	 * @param array $config User configuration
	 * @param boolean $run Whether Opauth should auto run after initialization.
	 */
	protected function _loadOpauth($config = null, $run = false){
		// Update dependent config in case the dependency is overwritten at app-level
		if (Configure::read('Opauth.callback_url') == '/connect/callback') {
			Configure::write('Opauth.callback_url', Configure::read('Opauth.path').'callback');
		}
	
		if (is_null($config)){
			$config = Configure::read('Opauth');
		}
	
		App::import('Vendor', 'Opauth.Opauth/lib/Opauth/Opauth');
		$this->Opauth = new Opauth($config, $run);
	}
}
