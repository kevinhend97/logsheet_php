<?php

namespace App\Controllers\Setting;

use App\Controllers\BaseController;
use App\Models\ApplicationSettingModel;
use App\Models\AssetStatusModel;
use App\Models\AssetModel;

class Application extends BaseController
{
    public function index()
    {
        $appSettingModel = new ApplicationSettingModel();
        $assetStatusModel = new AssetStatusModel();
        $assetModel = new AssetModel();
        $appSetting = $appSettingModel->findAll();
        $assetStatus = $assetStatusModel->orderBy('createdAt', 'asc')->getWhere('deletedAt', null)->getResultArray();
        $data = array(
            'title' => 'Setting Application',
            'subtitle' => 'Setting Application'
        );
        $data["breadcrumbs"] = [
			[
				"title"	=> "Home",
				"link"	=> "Dashboard"
			],
			[
				"title"	=> "Setting Application",
				"link"	=> "Application"
			],
		];
        $data['appSetting'] = $appSetting;
        $data['assetStatus'] = $assetStatus;
        return $this->template->render('Setting/Application/index.php', $data);
    }

    public function saveSetting()
    {
        $appSettingModel = new ApplicationSettingModel();
        $post = $this->request->getPost();
        $file = $this->request->getFile('appLogo');
        $appSettingId = $post['appSettingId'];
        $appSetting = $appSettingModel->where('appSettingId', $appSettingId)->get()->getResultArray();
        if (count($appSetting) > 0) {
            if ($file != null) {
                $name = 'LOGO_' . $file->getRandomName();
                $logoExist = $appSetting[0]['appLogo'];
                unlink('../public/assets/uploads/img/' . $logoExist);
                $file->move('../public/assets/uploads/img', $name);
                $data = array(
                    'userId' => $post['userId'],
                    'appName' => $post['appName'],
                    'appLogo' => $name,
                );
                $appSettingModel->update($appSettingId, $data);
                echo json_encode(array('status' => 'success', 'message' => 'You have successfully save data.', 'data' => $data));
            }else{
                $data = array(
                    'userId' => $post['userId'],
                    'appName' => $post['appName'],
                );
                $appSettingModel->update($appSettingId, $data);
                echo json_encode(array('status' => 'success', 'message' => 'You have successfully save data.', 'data' => $data));
            }
        }else{
            if ($post['appSettingId'] != '') {
                $name = 'LOGO_' . $file->getRandomName();
                $file->move('../public/assets/uploads/img', $name);
                $data = array(
                    'appSettingId' => $post['appSettingId'],
                    'userId' => $post['userId'],
                    'appName' => $post['appName'],
                    'appLogo' => $name,
                );
                $appSettingModel->insert($data);
                echo json_encode(array('status' => 'success', 'message' => 'You have successfully save data.', 'data' => $data));
            }else{
                echo json_encode(array('status' => 'failed', 'message' => 'Bad Request!', 'data' => $post));
            }
        }
        die();
    }

    public function saveStatus()
    {
        $assetStatusModel = new AssetStatusModel();
        $json = $this->request->getJSON();
        $statusName = $json->statusName;
        $statusUpdate = $json->statusUpdate;
        $statusDelete = $json->statusDelete;
        $lengthStatusUpdate = count($statusUpdate);
        $lengthStatusDelete = count($statusDelete);
        if ($lengthStatusUpdate > 0) {
            for ($i=0; $i < $lengthStatusUpdate; $i++) {
                $id = $statusUpdate[$i]->assetStatusId;
                $data = array(
                    'assetStatusName' => $statusUpdate[$i]->assetStatusName
                );
                $assetStatusModel->update($id, $data);
                echo json_encode(array('status' => 'success', 'message' => 'You have successfully save data.', 'data' => $statusUpdate));
            }
        }
        if ($lengthStatusDelete > 0) {
            for ($i=0; $i < $lengthStatusDelete; $i++) {
                $id = $statusDelete[$i];
                $assetStatusModel->deleteById($id);
                echo json_encode(array('status' => 'success', 'message' => 'You have successfully save data.', 'data' => $statusDelete));
            }
        }
        $lengthStatusName = count($json->statusName);
        if ($lengthStatusName > 0) {
            for ($i=0; $i < $lengthStatusName; $i++) { 
                $data = array(
                    'assetStatusId'     => $statusName[$i]->assetStatusId,
                    'userId'            => $statusName[$i]->userId,
                    'assetStatusName'   => $statusName[$i]->assetStatusName
                );
                $assetStatusModel->insert($data);
            }
            echo json_encode(array('status' => 'success', 'message' => 'You have successfully save data.', 'data' => $statusName));
        }
        die();
    }

    public function deleteAssetStatus()
    {
        $assetStatusModel = new AssetStatusModel();
        $assetModel = new AssetModel();
        $json = $this->request->getJSON();
        $id = $json->assetStatusId;
        $data = $assetModel->selectMin('createdAt')->where('assetStatusId', $id)->get()->getResultArray();
        if ($data[0]['createdAt'] != null) {
            echo json_encode(array('status' => 'exist', 'message' => 'This data already use since ', 'data' => $data[0]['createdAt']));
        }else{
            echo json_encode(array('status' => 'noexist', 'message' => '', 'data' => $json));
        }
        die();
    }
}
