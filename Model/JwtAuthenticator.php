<?php
/**
 * COmanage Registry Jwt Authenticator Model
 *
 * Portions licensed to the University Corporation for Advanced Internet
 * Development, Inc. ("UCAID") under one or more contributor license agreements.
 * See the NOTICE file distributed with this work for additional information
 * regarding copyright ownership.
 *
 * UCAID licenses this file to you under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v4.2.0
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

App::uses("AuthenticatorBackend", "Model");

class JwtAuthenticator extends AuthenticatorBackend {
  // Define class name for cake
  public $name = "JwtAuthenticator";

  // Required by COmanage Plugins
  public $cmPluginType = "authenticator";

  // Add behaviors
  public $actsAs = array('Containable');

  // Document foreign keys
  public $cmPluginHasMany = array(
    "CoPerson" => array("Jwt")
  );

  // Association rules from this model to other models
  public $belongsTo = array(
    "Authenticator"
  );

  public $hasMany = array(
    "JwtAuthenticator.Jwt" => array('dependent' => true)
  );

  // Default display field for cake generated views
  public $displayField = "authenticator_id";

  // Validation rules for table elements
  public $validate = array(
    'authenticator_id' => array(
      'rule' => 'numeric',
      'required' => true,
      'allowEmpty' => false
    )
  );

  // Do we support multiple authenticators per instantiation?
  public $multiple = false;

  // Do we support Self Service Reset?
  public $enableSSR = false;
  
  /**
   * Expose menu items.
   * 
   * @ since COmanage Registry v3.1.0
   * @ return Array with menu location type as key and array of labels, controllers, actions as values.
   */

  public function cmPluginMenus() {
    return array();
  }

  
  /**
   * Manage Authenticator data, as submitted from the view. Note this function does
   * not trigger provisioning of the new authenticator.
   *
   * @since  COmanage Registry v4.2.0
   * @param  Array   $data            Array of Authenticator data submitted from the view
   * @param  integer $actorCoPersonId Actor CO Person ID
   * @return string Human readable (localized) result comment
   * @throws InvalidArgumentException
   * @throws RuntimeException
   */

  public function manage($data, $actorCoPersonId, $actorApiUserId=null) {
    if(!$this->Jwt->save($data, array('provision' => false))) {
      throw new RuntimeException(_txt('er.db.save-a', array('Jwt')));
    }
  }

  /**
   * Reset Authenticator data for a CO Person.
   *
   * @since  COmanage Registry v3.1.0
   * @param  integer $coPersonId      CO Person ID
   * @param  integer $actorCoPersonId Actor CO Person ID
   * @param  integer $actorApiUserId  Actor API User ID
   * @return boolean true on success
   */
  
  public function reset($coPersonId, $actorCoPersonId, $actorApiUserId=null) {
    // Perform the reset. We simply delete any authenticators for the specified CO Person.

    $args = array();
    $args['conditions']['Jwt.jwt_authenticator_id'] = $this->pluginCfg['JwtAuthenticator']['id'];
    $args['conditions']['Jwt.co_person_id'] = $coPersonId;

    // Note deleteAll will not trigger callbacks by default
    $this->Jwt->deleteAll($args['conditions']);
    
    // And record some history

    $comment = _txt('pl.jwtauthenticator.reset',
                    array($this->pluginCfg['Authenticator']['description']));

    $this->Authenticator
         ->Co
         ->CoPerson
         ->HistoryRecord->record($coPersonId,
                                 null,
                                 null,
                                 $actorCoPersonId,
                                 ActionEnum::AuthenticatorDeleted,
                                 $comment,
                                 null, null, null, null,
                                 $actorApiUserId);

    // We always return true
    return true;
  }

  /**
   * Obtain the current Authenticator status for a CO Person.
   *
   * @since  COmanage Registry v3.1.0
   * @param  integer $coPersonId   CO Person ID
   * @return Array Array with values
   *               status: AuthenticatorStatusEnum
   *               comment: Human readable string, visible to the CO Person
   */

  public function status($coPersonId) {
    // Is there a Jwt for this person?

    $args = array();
    $args['conditions']['Jwt.jwt_authenticator_id'] = $this->pluginCfg['JwtAuthenticator']['id'];
    $args['conditions']['Jwt.co_person_id'] = $coPersonId;
    $args['contain'] = false;
    
    $jwt = $this->Jwt->find('first', $args);
    
    if(!empty($jwt['Jwt']['modified'])) {
      list($headersB64, $payloadB64, $sig) = explode('.', $jwt['Jwt']['jwt']);

      $payloadB64 = str_replace('_', '/', str_replace('-', '+', $payloadB64));

      $payload = json_decode(base64_decode($payloadB64));

      return array(
        'status' => AuthenticatorStatusEnum::Active,
        // Note we don't currently have access to local timezone setting (see OrgIdentity for example)
        'comment' => _txt('pl.jwtauthenticator.mod', array($jwt['Jwt']['modified'])),
        'payload' => $payload
      );
    }

    return array(
      'status' => AuthenticatorStatusEnum::NotSet,
      'comment' => _txt('fd.set.not'),
      'payload' => array()
    );
  }
}
