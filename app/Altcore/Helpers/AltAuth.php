<?php

namespace App\Altcore\Helpers;

class AltAuth
{
  private $user;
  private $scopes;
  private $isSuperadmin;
  
  public function __construct($user,$scopes,$isSuperadmin=false)
  {
    $this->user = $user;
    $this->scopes = $scopes;
    $this->isSuperadmin = $isSuperadmin;
  }

  public function can($scope)
  {
    if($this->isSuperadmin) return true;
    // dd($this->scopes);
    return in_array($scope,$this->scopes);
  }

  public function authorize($scope)
  {
    if(!$this->can($scope)) return abort(401,'Permission Denied for scope '.$scope);
  }

  public function isSuperadmin()
  {
    return $this->isSuperadmin;
  }

  public static function check()
  {
    $a = new AltAuth(
      request()->_auth->user,
      request()->_auth->scopes,
      request()->_superadmin
    );
    return $a;
  }
}