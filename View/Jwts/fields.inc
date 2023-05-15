<!--
/**
 * COmanage Registry Jwt Authenticator Plugin Fields
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
 * @since         COmanage Registry v3.1.0
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */
-->
<?php
  // Determine if fields are editable
  $e = false;
  
  if($this->action == "manage" && $permissions['manage'])
    $e = true;

  print $this->Form->hidden('jwt_authenticator_id',
                            array('default' => $vv_authenticator['JwtAuthenticator']['id'])) . "\n";
  print $this->Form->hidden('co_person_id', array('default' => $vv_co_person['CoPerson']['id'])) . "\n";
  
  // Add breadcrumbs
  print $this->element("coCrumb", array('authenticator' => 'Jwt'));
?>

<ul id="<?php print $this->action; ?>_jwt" class="fields form-list form-list-admin">
  <li>
    <div class="field-name">
      <?php print _txt('JSON Web Token'); ?><span class="required">*</span>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('jwt', array('class' => 'focusFirst')) : filter_var($jwts[0]['Jwt']['jwt'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>

    <?php if($e): ?>
    <li class="fields-submit">
      <div class="field-name">
        <span class="required"><?php print _txt('fd.req'); ?></span>
      </div>
      <div class="field-info">
        <?php print $this->Form->submit($submit_label); ?>
      </div>
    </li>
  <?php endif; ?>


</ul>
