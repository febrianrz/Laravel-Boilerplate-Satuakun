<?php

namespace App\Altcore\Helpers;

class Form
{
  private $user;
  private $request;

  public function __construct($request,$user)
  {
    $this->request = $request;
    $this->user = $user;
  }

  public function data($params,$json_params=[])
  {
    $data = $this->request->only($params);
    if(count($json_params)>0){
      foreach($json_params as $p){
        $data[$p] = json_decode($this->request->input($p));
      }
    }

    if($this->request->getMethod() == "POST"){
      $data['created_by_id']  = $this->user->user->id;
      $data['created_by']     = [
        'id'      => $this->user->user->id,
        'name'    => $this->user->user->name,
        'email'    => $this->user->user->email,
        'id_karyawan'=>$this->user->user->unique_key,
        'role'    => $this->user->role,
      ];
    } else if($this->request->getMethod() == "PUT"){
      $data['updated_by_id']  = $this->user->user->id;
      $data['updated_by']     = [
        'id'      => $this->user->user->id,
        'name'    => $this->user->user->name,
        'email'    => $this->user->user->email,
        'id_karyawan'=>$this->user->user->unique_key,
        'role'    => $this->user->role,
      ];
    }
    return $data;
  }

  public function delete($row)
  {
    if($this->request->getMethod() == "DELETE"){
      $row->deleted_by_id = $this->user->user->id;
      $row->deleted_by = [
        'id'      => $this->user->user->id,
        'name'    => $this->user->user->name,
        'email'    => $this->user->user->email,
        'id_karyawan'=>$this->user->user->unique_key,
        'role'    => $this->user->role,
      ];
      
      $row->save();
    }
  }
}