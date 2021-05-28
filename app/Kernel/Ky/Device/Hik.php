<?php
/**
 * Created by PhpStorm.
 * Script Name: Hik.php
 * Create: 2021/3/16 15:55
 * Description:
 * Author: fudaoji<fdj@kuryun.cn>
 */
namespace App\Kernel\Ky\Device;
use App\Service\Dao\UserHouseDao;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\CoroutineHandler;

class Hik
{
    private $client;
    private $options = [];
    private $baseUri = 'https://api2.hik-cloud.com';
    private $clientId = '';
    private $clientSecret = '';
    private $errMsg = '';
    private $accessToken = '';
    private $roleMap = [UserHouseDao::ROLE_YZ => 1, UserHouseDao::ROLE_CY => 3];

    public function __construct($options = [])
    {
        $this->options = array_merge($this->options, $options);
        if(!empty($this->options['access_token'])){
            $this->accessToken = $this->options['access_token'];
        }
        //$this->clientId = $this->options['client_id'];
        //$this->clientSecret = $this->options['client_secret'];
    }

    /**
     * 消费信息
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function mqConsumerMessages(array $params)
    {
        $url = '/api/v1/mq/consumer/messages';
        $data = [
            'consumerId' => $params['consumer_id'],
            'autoCommit' => isset($params['auto_commit']) ? $params['auto_commit'] : false
        ];

        return $this->request([
            'url' => $url,
            'content_type' => 'form_params',
            'data' => $data
        ]);
    }

    /**
     * 创建消费者
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function mqConsumerGroup1()
    {
        $url = '/api/v1/mq/consumer/group1';
        $data = [

        ];

        return $this->request([
            'url' => $url,
            'data' => $data
        ]);
    }

    /**
     * 人脸删除
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function facesDelete(array $params)
    {
        $url = '/api/v1/open/basic/faces/delete?employeeNo=' . $params['user_unionid'];
        $data = [

        ];

        return $this->request([
            'url' => $url,
            'data' => $data
        ]);
    }

    /**
     * 获取访客的二维码
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function visitorsGetQrcode(array $params)
    {
        $data = [
            "cardNo" => $params['card_no'],
            'effectTime' => $params['begin_time'],
            'expireTime' => $params['end_time'],
            'openTimes' => empty($params['open_times']) ? 1 : $params['open_times']
        ];
        $url = '/api/v1/community/access/visitors/actions/getQrcode';
        /**
         * cardNo=12345&effectTime=181205180000&expireTime=181205200000&openTimes=4
         */
        return $this->request([
            'url' => $url,
            'content_type' => 'form_params',
            'data' => $data
        ]);
    }

    /**
     * 删除卡片
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function cardBatchDelete(array $params)
    {
        $url = '/api/v1/open/basic/cards/batchDelete';
        /**
         * [{"cardNo": "3423423","cardType": "normalCard","employeeNo": "321"}]
         */
        $data = [
            "cardNos" => $params['card_no_list']
        ];

        return $this->request([
            'url' => $url,
            'data' => $data
        ]);
    }

    /**
     * 新增卡片
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function cardAdd(array $params)
    {
        $url = '/api/v1/open/basic/cards/batchCreate';
        /**
         * [{"cardNo": "3423423","cardType": "normalCard","employeeNo": "321"}]
         */
        $data = [
            "cards" => $params['cards']
        ];

        return $this->request([
            'url' => $url,
            'data' => $data
        ]);
    }

    /**
     * 权限组-查询下发失败
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function searchStatus(array $params)
    {
        $url = '/api/v1/open/accessControl/allots/actions/searchStatus';
        $data = [
            "groupId" => $params['permission_group_id'],
            "pageNo" => empty($params['current_page']) ? 1 : $params['current_page'],
            "pageSize" => empty($params['page_size']) ? 100 : $params['page_size'],
        ];

        return $this->request([
            'url' => $url,
            'data' => $data
        ]);
    }

    /**
     * 远程开门
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function remoteControlOpen(array $params)
    {
        $url = '/api/v1/open/accessControl/remoteControl/actions/open';
        $data = [
            "deviceSerial" => $params['serial']
        ];

        return $this->request([
            'url' => $url,
            'data' => $data
        ]);
    }

    /**
     * 权限组-下发权限
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function issuedByGroup(array $params)
    {
        $url = '/api/v1/open/accessControl/allots/actions/issuedByGroup';
        $data = [
            "groupId" => $params['permission_group_id']
        ];

        return $this->request([
            'url' => $url,
            'data' => $data
        ]);
    }

    /**
     * 权限组-获取设备序列号列表
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function permissionGroupGetDeviceSerials(array $params)
    {
        $url = '/api/v1/open/accessControl/permissionGroups/actions/getDeviceSerials';
        $data = [
            "groupId" => $params['permission_group_id'],
            "pageNo" => empty($params['current_page']) ? 1 : $params['current_page'],
            "pageSize" => empty($params['page_size']) ? 100 : $params['page_size'],
        ];

        return $this->request([
            'url' => $url,
            'data' => $data
        ]);
    }

    /**
     * 权限组-获取人员编号列表
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function permissionGroupGetEmployeeNos(array $params)
    {
        $url = '/api/v1/open/accessControl/permissionGroups/actions/getEmployeeNos';
        $data = [
            "groupId" => $params['permission_group_id'],
            "pageNo" => empty($params['current_page']) ? 1 : $params['current_page'],
            "pageSize" => empty($params['page_size']) ? 100 : $params['page_size'],
        ];

        return $this->request([
            'url' => $url,
            'data' => $data
        ]);
    }

    /**
     * 权限组-解绑设备
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function permissionGroupRemoveDevices(array $params)
    {
        $url = '/api/v1/open/accessControl/permissionGroups/actions/removeDevices';
        $data = [
            "groupId" => $params['permission_group_id'],
            'deviceSerials' => $params['serial_list']
        ];

        return $this->request([
            'url' => $url,
            'data' => $data
        ]);
    }

    /**
     * 权限组-绑定设备
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function permissionGroupAddDevices(array $params)
    {
        $url = '/api/v1/open/accessControl/permissionGroups/actions/addDevices';
        $data = [
            "groupId" => $params['permission_group_id'],
            "checkCapability" => true,
            'deviceSerials' => $params['serial_list']
        ];

        return $this->request([
            'url' => $url,
            'data' => $data
        ]);
    }

    /**
     * 权限组-解绑人员
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function permissionGroupRemovePersons(array $params)
    {
        $url = '/api/v1/open/accessControl/permissionGroups/actions/removePersons';
        $data = [
            "groupId" => $params['permission_group_id'],
            'employeeNos' => $params['employee_list']
        ];

        return $this->request([
            'url' => $url,
            'data' => $data
        ]);
    }

    /**
     * 权限组-绑定人员
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function permissionGroupAddPersons(array $params)
    {
        $url = '/api/v1/open/accessControl/permissionGroups/actions/addPersons';
        $data = [
            "groupId" => $params['permission_group_id'],
            'employeeNos' => $params['employee_list']
        ];

        return $this->request([
            'url' => $url,
            'data' => $data
        ]);
    }

    /**
     * 权限组-删除
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function permissionGroupDelete(array $params)
    {
        $url = '/api/v1/open/accessControl/permissionGroups/delete?groupId=' . $params['group_id'];
        $data = [

        ];

        return $this->request([
            'url' => $url,
            'data' => $data
        ]);
    }

    /**
     * 权限组-列表
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function permissionGroupPage(array $params)
    {
        $url = '/api/v1/open/accessControl/permissionGroups/actions/page';
        $data = [
            "pageNo" => empty($params['current_page']) ? 1 : $params['current_page'],
            "pageSize" =>  empty($params['page_size']) ? 10 : $params['page_size']
        ];

        return $this->request([
            'url' => $url,
            'data' => $data
        ]);
    }

    /**
     * 权限组-增加
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function permissionGroupAdd(array $params)
    {
        $url = '/api/v1/open/accessControl/permissionGroups/create';
        $data = [
            "groupName" => $params['group_name']
        ];

        return $this->request([
            'url' => $url,
            'data' => $data
        ]);
    }

    /**
     * 获取人员信息
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function personGet(array $params)
    {
        $url = '/api/v1/open/basic/persons/get';
        $data = [
            "employeeNo" => $params['id']
        ];

        return $this->request([
            'url' => $url,
            'method' => 'get',
            'data' => $data
        ]);
    }

    /**
     * 更新人员信息
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function personUpdate(array $params)
    {
        $url = '/api/v1/open/basic/persons/update';
        $data = [
            "employeeNo" => $params['id'],
            "personName" => $params['username']
        ];

        !empty($params['mobile']) && $data['personPhone'] = $params['mobile'];
        !empty($params['face_base64']) && $data['faceImageBase64'] = $params['face_base64'];
        isset($params['need_verify']) && $data['verifyImage'] = $params['need_verify'];
        !empty($params['group_id']) && $data['belongGroup'] = $params['group_id'];
        //return  $data;
        return $this->request([
            'url' => $url,
            'data' => $data
        ]);
    }

    /**
     * 新增人员
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function personAdd(array $params)
    {
        $url = '/api/v1/open/basic/persons/create';
        $data = [
            "employeeNo" => $params['id'],
            "personName" => empty($params['username']) ? ('yll' . time()) : $params['username']
        ];

        !empty($params['mobile']) && $data['personPhone'] = $params['mobile'];
        !empty($params['face_base64']) && $data['faceImageBase64'] = $params['face_base64'];
        isset($params['need_verify']) && $data['verifyImage'] = $params['need_verify'];
        !empty($params['group_id']) && $data['belongGroup'] = $params['group_id'];
        return $this->request([
            'url' => $url,
            'data' => $data
        ]);
    }

    /**
     * 新增组（社区）
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function groupAdd(array $params)
    {
        $url = '/api/v1/open/basic/groups/create';
        return $this->request([
            'url' => $url,
            'data' => [
                "groupNo" => $params['group_no'],
                "groupName" => $params['group_name'],
                "parentNo" => empty($params['parent_no']) ? '' : $params['parent_no']
            ]
        ]);
    }

    /**
     * 注销设备
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function deviceDelete(array $params)
    {
        $url = 'api/v1/open/basic/devices/delete?deviceSerial=' . $params['serial'];
        return $this->request([
            'url' => $url,
            'data' => [
            ]
        ]);
    }

    /**
     * 新增设备
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function deviceAdd(array $params)
    {
        $url = '/api/v1/open/basic/devices/create';
        return $this->request([
            'url' => $url,
            'data' => [
                "groupNo" => $params['group_no'],
                "deviceSerial" =>  $params['serial'],
                "validateCode" =>  $params['validate_code']
            ]
        ]);
    }

    /**
     * 移除人员所属户室
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function roomRelationDel(array $params)
    {
        $url = '/api/v1/estate/system/person/actions/deleteRoomRelation';
        return $this->request([
            'url' => $url,
            'data' => [
                "personId" => $params['person_id'],
                "roomId" =>  $params['room_id']
            ]
        ]);
    }

    /**
     * 设置人员所属户室
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function roomRelationAdd(array $params)
    {
        $url = '/api/v1/estate/system/person/actions/addRoomRelation';
        return $this->request([
            'url' => $url,
            'data' => [
                "personId" => $params['person_id'],
                "roomId" =>  $params['room_id'],
                'identityType' => $this->roleMap[$params['identity_type']],
                'checkInDate' => $params['check_in_date'], //入驻时间  2021-02-03
                'checkOutDate' => empty($params['check_out_date']) ? '' : $params['check_out_date']
            ]
        ]);
    }

    /**
     * 设置人员所属社区
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function communityRelationAdd(array $params)
    {
        $url = '/api/v1/estate/system/person/actions/addCommunityRelation';
        return $this->request([
            'url' => $url,
            'data' => [
                "personId" => $params['person_id'],
                "communityId" =>  $params['community_id'],
            ]
        ]);
    }

    /**
     * 新增人员-废弃
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function personAddBak(array $params)
    {
        $url = '/api/v1/estate/system/person';
        return $this->request([
            'url' => $url,
            'data' => [
                "unionId" => empty($params['union_id']) ? '' : $params['union_id'],
                "personName" =>  $params['username'],
                "mobile" =>  $params['mobile'],
                "gender" => isset($params['sex']) ? $params['union_id'] : -1,
                "credentialType" =>  empty($params['credential_type']) ? 1 : $params['credential_type'],
                'credentialNumber' => empty($params['credential_number']) ? '' : $params['credential_number'],
                'faceUrl' => empty($params['face_url']) ? '' : $params['face_url'],
                'birthday' => empty($params['birthday']) ? '' : $params['birthday'],
                'personRemark' => empty($params['remark']) ? '' : $params['remark'],
                'nation' => empty($params['nation']) ? '' : $params['nation'],
                'educationLevel' => empty($params['educationLevel']) ? 6 : $params['educationLevel'],
                'workUnit' => empty($params['workUnit']) ? '' : $params['workUnit'],
                'position' => empty($params['position']) ? '' : $params['position'],
                'religion' => empty($params['religion']) ? '' : $params['religion'],
                'englishName' => empty($params['english_name']) ? '' : $params['english_name'],
                'email' => empty($params['email']) ? '' : $params['email'],
                'addressDetail' => empty($params['address']) ? '' : $params['address'],
                'provinceCode' => empty($params['province_code']) ? '' : $params['province_code'],
                'cityCode' => empty($params['city_code']) ? '' : $params['city_code'],
                'countyCode' => empty($params['county_code']) ? '' : $params['county_code']
            ]
        ]);
    }

    /**
     * 新增房屋
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function houseAdd(array $params)
    {
        $url = '/api/v1/estate/system/rooms';
        return $this->request([
            'url' => $url,
            'data' => [
                "unionId" => empty($params['union_id']) ? '' : $params['union_id'],
                "unitId" =>  $params['unit_id'],
                "floorNumber" => $params['floor'],
                "roomNumber" =>  $params['house_number'],
                'roomName' => empty($params['title']) ? '' : $params['title']
            ]
        ]);
    }

    /**
     * 新增单元
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function unitAdd(array $params)
    {
        $url = '/api/v1/estate/system/units';
        return $this->request([
            'url' => $url,
            'data' => [
                "unionId" => empty($params['union_id']) ? '' : $params['union_id'],
                "buildingId" =>  $params['building_id'],
                "unitName" =>  empty($params['unit_name']) ? '' : $params['unit_name'],
                "unitNumber" =>  $params['unit_number']
            ]
        ]);
    }

    /**
     * 新增楼宇
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function buildingAdd($params = []){
        $url = '/api/v1/estate/system/buildings';
        return $this->request([
            'url' => $url,
            'data' => [
                "unionId" => empty($params['union_id']) ? '' : $params['union_id'],
                'communityId' => $params['community_id'],
                "buildingName" =>  $params['building_name'],
                "buildingNumber" =>  $params['building_number'],
                "floorUpCount" =>  $params['floor_num'],
                "floorDownCount"=>  empty($params['floor_down_num']) ? '' : $params['floor_down_num'],
                "floorFamilyCount" => empty($params['floor_family_num']) ? 20 : $params['floor_family_num'],
                "buildingUnitSize" => empty($params['unit_size']) ? 5 : $params['unit_size'],
                "buildingRemark"=> empty($params['remark']) ? '' : $params['remark']
            ]
        ]);
    }

    /**
     * 社区列表
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function communityList($params = []){
        $url = '/api/v1/estate/system/communities/actions/list';
        return $this->request([
            'url' => $url,
            'method' => 'get',
            'data' => [
                "pageNo" => empty($params['current_page']) ? 1 : $params['current_page'],
                "pageSize" =>  empty($params['page_size']) ? 10 : $params['page_size']
            ]
        ]);
    }

    /**
     * 新增社区
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function communityAdd($params = []){
        $url = '/api/v1/estate/system/communities';
        return $this->request([
            'url' => $url,
            'data' => [
                "unionId" => empty($params['union_id']) ? '' : $params['union_id'],
                "communityName" =>  $params['community_name'],
                "provinceCode" =>  $params['province_code'],
                "cityCode" =>  empty($params['city_code']) ? '' : $params['city_code'],
                "countyCode"=>  empty($params['county_code']) ? '' : $params['county_code'],
                "addressDetail" => $params['address'],
                "communitySquareMeter" => empty($params['square_meter']) ? '' : $params['square_meter'],
                "longitude" => empty($params['longitude']) ? '' : $params['longitude'],
                "latitude"=> empty($params['latitude']) ? '' : $params['latitude'],
                "chargePersonId"=> empty($params['charge_id']) ? '' : $params['charge_id'],
                "phoneNumber" => empty($params['charge_tel']) ? '' : $params['charge_tel'],
                "communityRemark"=> empty($params['remark']) ? '' : $params['remark']
            ]
        ]);
    }

    /**
     * 获取access_token
     * @param array $params
     * @return bool|mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function oauthToken(array $params){
        $url = '/oauth/token';
        return $this->request([
            'url' => $url,
            'content_type' => 'form_params',
            'func' => 'oauthToken',
            'data' => [
                'grant_type' => 'client_credentials',
                'client_id' => $params['client_id'],
                'client_secret' => $params['client_secret']
            ]
        ]);
        return  ['code' => 1, 'data' => $data];
    }

    private function request($params = []){
        $this->client = new Client([
            'base_uri' => empty($this->options['base_uri']) ? $this->baseUri : $this->options['base_uri'],
            'handler' => HandlerStack::create(new CoroutineHandler()),
            'timeout' => empty($this->options['timeout']) ? 10 : $this->options['timeout']
        ]);
        $method = empty($params['method']) ? 'post' : $params['method'];
        $extra = [
            'http_errors' => false
        ];

        if(empty($params['func'])){ //排除获取token接口
            $extra['headers'] = [
                'Content-Type'     => 'application/json;charset=UTF-8',
                'Authorization' => 'bearer ' . $this->accessToken
            ];
        }

        if(!empty($params['data'])){
            if(isset($params['content_type']) && $params['content_type'] === 'form_params'){
                $extra['form_params'] = $params['data'];
                $extra['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
            }else{
                switch ($method){
                    case 'get':
                        $params['url'] .= '?' . http_build_query($params['data']);
                        break;
                    default:
                        $extra['json'] = $params['data'];
                        break;
                }
            }
        }

        $response = $this->client->request($method, $params['url'], $extra);

        if($response->getStatusCode() !== 200){
            $this->setError($response->getStatusCode());
            return false;
        }
        $return = json_decode($response->getBody()->getContents(), true);

        if(isset($return['code'])){
            if($return['code'] == 200){
                return isset($return['data']) ? $return['data'] : $return;
            }else{
                $this->setError($return['code']);
                return false;
            }
        }
        return $return;
    }

    public function setError($code = 200){
        $list = [
            401 => '获取token失败',
            404 => '接口路径与请求方式错误',
            429 => '接口请求频率超过限制',
            500 => '服务端错误',
            510001 => '参数错误',
            510105	=> '组不存在',
            511011 =>'证件类型为空',
            511012 => '证件号为空',
            511046 => '该人员不存在',
            511023 => '住户已存在',
            511157 => '该设备已被添加',
            511161 => '设备不在线',
            519999 => '操作失败(9610034: 您尚未购买此项服务)',
            514002 => '无效的consumerId'
        ];
        $this->errMsg = isset($list[$code]) ? ($code . ':' .$list[$code]) : ($code.':未知错误');
    }

    public function getError(){
        return $this->errMsg;
    }
}