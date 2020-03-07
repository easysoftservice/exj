<?php
/**
 * @class AppRolUnassignedUsersController
 * Controlador para Rol y Usuarios
 */
class AppRolUnassignedUsersController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		global $exj;
		$response = new ExjResponse();
		
		$topics=null;
		$total=0;
		
	//	print_r($this->params);
		
		if (!AppRolUserModel::LoadListRolUnassignedUsers($response, $topics, $total, $this->params)) {
			return $response;
		}
	//	$response->setMsgInfo(__METHOD__);
		$response->setDataTopics($topics, $total);
		return $response;
	}
	
	/**
     * overwrited. Lectura del modelo de lista
     *
     * @param string $nameListModel
     * @param array $params
     * @param bool $addItemsTopbarExtras
     */
	protected function listModelRead(&$nameListModel, &$params, &$addItemsTopbarExtras = false) {
		$nameListModel = 'rol_unassigned_users';
		$params[] = 'gid';
		$addItemsTopbarExtras = true;
	}	
	
	/**
	 * override. Creación
	 */
	public function create() {
		$response = new ExjResponse();
		
		if (!$this->isValidParamsToCreate($response)) {
			return $response;
		}
		
		$response->setMsgError("No implementado " . __METHOD__);
		
		return $response;
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		$response = new ExjResponse();
		
		if (!$this->isValidParamsToUpdate($response)) {
			return $response;
		}
		
		$response->setMsgError("No implementado " . __METHOD__);
		
		return $response;
	}

	/**
	 * override. Destruír o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		$response->setMsgError("No implementado " . __METHOD__);
		
	//	AppRolUserModel::destroy($this->id, 'AppRolUserEditableModel', $response, '', 'rol_user');
		
		return $response;
	}
	
	
	
	public function addUser(){
		$response = new ExjResponse();
		
		$id_user = $this->id;
		if (!$id_user) {
			$response->setMsgError("No se indicó id!");
			return $response;
		}
		
		$jusr_gid = $this->getParam('jusr_gid', 0);
		if (!$jusr_gid) {
			$response->setMsgError("No se indicó grupoID!");
			return $response;
		}
		
	//	echo "id_user: $id_user";
		AppRolUserModel::AddUserUnassigned($response, $id_user, $jusr_gid);
		
		if (!$response->haveMsgText()) {
			$response->setMsgNotify("Assigned User!!!");
		}
		
		return $response;
	}
	
	public function delUser(){
		$response = new ExjResponse();
		
		$id_sys_user = $this->id;
		if (!$id_sys_user) {
			$response->setMsgError("No se indicó ID!");
			return $response;
		}
		
		AppRolUserModel::DeleteUserSys($response, $id_sys_user);
		
		if (!$response->haveMsgText()) {
			$response->setMsgNotify("Deleted User!!!");
		}
		
		return $response;
	}
}

?>