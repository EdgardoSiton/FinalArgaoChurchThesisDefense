<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Google\Service\ArtifactRegistry;

class GoogleDevtoolsArtifactregistryV1Rule extends \Google\Model
{
  /**
   * @var string
   */
  public $action;
  protected $conditionType = Expr::class;
  protected $conditionDataType = '';
  /**
   * @var string
   */
  public $name;
  /**
   * @var string
   */
  public $operation;
  /**
   * @var string
   */
  public $packageId;

  /**
   * @param string
   */
  public function setAction($action)
  {
    $this->action = $action;
  }
  /**
   * @return string
   */
  public function getAction()
  {
    return $this->action;
  }
  /**
   * @param Expr
   */
  public function setCondition(Expr $condition)
  {
    $this->condition = $condition;
  }
  /**
   * @return Expr
   */
  public function getCondition()
  {
    return $this->condition;
  }
  /**
   * @param string
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
  /**
   * @param string
   */
  public function setOperation($operation)
  {
    $this->operation = $operation;
  }
  /**
   * @return string
   */
  public function getOperation()
  {
    return $this->operation;
  }
  /**
   * @param string
   */
  public function setPackageId($packageId)
  {
    $this->packageId = $packageId;
  }
  /**
   * @return string
   */
  public function getPackageId()
  {
    return $this->packageId;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleDevtoolsArtifactregistryV1Rule::class, 'Google_Service_ArtifactRegistry_GoogleDevtoolsArtifactregistryV1Rule');