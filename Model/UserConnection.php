<?php

/**
 * UserConnection
 */
class UserConnection extends SocialAppModel {
	
	public $belongsTo = array(
		'User' => array(
			'className' => 'Users.User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
		)
	);	
}
