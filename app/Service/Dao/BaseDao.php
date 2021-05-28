<?php
/**
 * Created by PhpStorm.
 * Script Name: BaseDao.php
 * Create: 9:12 下午
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */
namespace App\Service\Dao;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;

class BaseDao
{
    /**
     * 自增主键约定
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型实例
     * @var string
     */
    protected $model = '';

    /**
     * 自己维护记录时间戳
     * @var bool
     */
    protected $autoWriteTimestamp = true;

    /**
     * 创建时间字段
     * @var string
     */
    protected $createTime = 'create_time';

    /**
     * 更新时间字段
     * @var string
     */
    protected $updateTime = 'update_time';

    /**
     * 是否缓存
     * @var bool
     */
    protected $isCache = false;

    /**
     * 缓存时间
     * @var int
     */
    protected $expire = 3600;

    /**
     * 缓存key统一前缀
     * Author: Jason<dcq@kuryun.cn>
     */
    private function prefixCachekey()
    {
        return $this->model->getConnectionName() . '::' . $this->model->getTable();
    }

    /**
     * 初始模型实例
     * Author: Jason<dcq@kuryun.cn>
     */
    public function __construct()
    {
        $key = substr(get_called_class(), strripos(get_called_class(), '\\') + 1);
        if (substr($key, -3) == 'Dao') {
            $key = ucfirst(substr($key, 0, strlen($key) - 3));
            $file_name = BASE_PATH . "/app/Model/{$key}.php";
            $class_name = "App\\Model\\{$key}";
            if (file_exists($file_name)) {
                return $this->model = make($class_name);
            }
        }
        throw new BusinessException(ErrorCode::SERVER_ERROR, "模型{$key}不存在，文件不存在！");
    }

    /**
     * sum 关联查询求和
     * @param array $params
     * @return mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function sumsJoin($params = [])
    {
        ksort($params);
        $where = empty($params['where']) ? [] : $params['where'];
        $field = $params['field'];
        $refresh = empty($params['refresh']) ? 0 : $params['refresh'];
        unset($params['refresh']);
        $cache_key = md5($this->prefixCachekey() . __FUNCTION__ . serialize($params));
        if (cache()->has($cache_key)) {
            if (!$refresh) {
                return cache()->get($cache_key);
            }
            cache()->delete($cache_key);
        }
        $query = $this->whereBuilder($where);
        $this->_join($query, $params['join']);
        $data = $query->sum($field);
        $this->isCache && cache()->set($cache_key, $data, $this->expire);
        return $data;
    }

    /**
     * 获取有联合查询的分页数据
     * @param array $params
     * @return mixed
     * e.g: $this->dao->getListJoin([
     * 'join' => [['user as u', 'dao.user_id=u.id', 'inner|left|right']],
     * 'limit' => [1, 100],
     * 'where' => ['dao.id' => ['gt', 300]],
     * 'field' => ['u.username', 'dao.id as activity_id'],
     * 'order' => ['dao.id', 'desc']
     * ]);
     * Author: Jason<dcq@kuryun.cn>
     */
    public function getFieldJoin($params = []){
        ksort($params);
        $where = empty($params['where']) ? [] : $params['where'];
        $order = empty($params['order']) ? [] : $params['order'];
        $field = empty($params['field']) ? true : $this->_field($params['field']);
        $refresh = empty($params['refresh']) ? 0 : $params['refresh'];
        unset($params['refresh']);
        $cache_key = md5($this->prefixCachekey() . __FUNCTION__ . serialize($params));
        if (cache()->has($cache_key)) {
            if (!$refresh) {
                return cache()->get($cache_key);
            }
            cache()->delete($cache_key);
        }
        $query = $this->whereBuilder($where);
        $this->_join($query, $params['join']);
        if(!empty($order)){
            $query = $this->_order($query, $order);
        }
        $data = $query->pluck(...$field)->toArray();
        $this->isCache && cache()->set($cache_key, $data, $this->expire);
        return $data;
    }

    /**
     * 根据条件更新数据
     * @param array $where
     * @param array $data
     * @return bool|mixed
     * Author: Jason<dcq@kuryun.cn>
     */
    public function updateByMap($where = [], $data = [])
    {
        if ($this->autoWriteTimestamp) {
            $this->updateTime && empty($data[$this->updateTime]) && $data[$this->updateTime] = time();
        }
        return  $this->whereBuilder($where)->update($data);
    }

    /**
     * 根据主键获取单个数据
     * @param int $id
     * @param int $refresh 是否刷新缓存
     * @return mixed
     * Author: Jason<dcq@kuryun.cn>
     */
    public function getOne($id = 0, $refresh = 0)
    {
        $cache_key = md5($this->prefixCachekey() . '::' . $id);
        if (cache()->has($cache_key)) {
            if (!$refresh) {
                return cache()->get($cache_key);
            }
            cache()->delete($cache_key);
        }
        $data = $this->model->query()->find($id);
        $this->isCache && cache()->set($cache_key, $data, $this->expire);

        return $data;
    }

    /**
     * 新增单条数据
     * @param array $data
     * @return bool|mixed
     * Author: Jason<dcq@kuryun.cn>
     */
    public function addOne($data = [])
    {
        if ($this->autoWriteTimestamp) {
            $this->createTime && empty($data[$this->createTime]) && $data[$this->createTime] = time();
            $this->updateTime && empty($data[$this->updateTime]) && $data[$this->updateTime] = time();
        }
        if ($last_id = $this->model->query()->insertGetId($data)) {
            return $this->getOne($last_id);
        }

        return false;
    }

    /**
     * 批量添加
     * @param array $arr 二维数组
     * @return bool
     * Author: Jason<dcq@kuryun.cn>
     */
    public function addBatch($arr = [])
    {
        if ($this->autoWriteTimestamp) {
            foreach ($arr as &$item) {
                $this->createTime && empty($item[$this->createTime]) && $item[$this->createTime] = time();
                $this->updateTime && empty($item[$this->updateTime]) && $item[$this->updateTime] = time();
            }
        }
        return $this->model->query()->insert($arr);
    }

    /**
     * 更新单条数据
     * @param array $data
     * @return bool|mixed
     * Author: Jason<dcq@kuryun.cn>
     */
    public function updateOne($data = [])
    {
        if (!isset($data[$this->pk])) {
            return false;
        }
        if ($this->autoWriteTimestamp) {
            $this->updateTime && empty($data[$this->updateTime]) && $data[$this->updateTime] = time();
        }
        $res = $this->model->query()->where([$this->pk => $data[$this->pk]])->update($data);
        if ($res) {
            //刷新缓存
            return $this->getOne($data[$this->pk], 1);
        }

        return false;
    }

    /**
     * 批量更新
     * @param $arr
     * @return bool
     * Author: Jason<dcq@kuryun.cn>
     */
    public function updateBatch($arr)
    {
        $count = 0;
        foreach ($arr as $data) {
            $this->updateOne($data) && $count++;
        }

        return $count == count($arr);
    }

    /**
     * 主键删除单个数据
     * @param int $id
     * @return bool
     * Author: Jason<dcq@kuryun.cn>
     */
    public function delOne($id = 0)
    {
        $res = $this->model->destroy($id);
        if ($res) {
            $cache_key = md5($this->prefixCachekey() . '::' . serialize($id));
            if (cache()->has($cache_key)) {
                cache()->delete($cache_key);
            }
            return true;
        }

        return false;
    }

    /**
     * 主键批量删除
     * @param $arr //主键数组
     * @return bool
     * Author: Jason<dcq@kuryun.cn>
     */
    public function delBatch($arr)
    {
        return $this->delOne($arr);
    }

    /**
     * 根据条件删除数据
     * @param $where
     * @return bool
     * Author: Jason<dcq@kuryun.cn>
     */
    public function delByMap($where)
    {
        return $this->whereBuilder($where)->delete();
    }

    /**
     * 根据条件获取数据
     * @param array $where
     * @param int $refresh
     * @param array $field
     * @return mixed
     * Author: Jason<dcq@kuryun.cn>
     */
    public function getOneByMap($where = [], $refresh = 0, $field = [])
    {
        $cache_key = md5($this->prefixCachekey() . '::getonebymap::' . serialize($where));
        if (cache()->has($cache_key)) {
            if (!$refresh) {
                return cache()->get($cache_key);
            }
            cache()->delete($cache_key);
        }
        $query = $this->whereBuilder($where);
        if (!empty($field)) {
            $query = $query->select($field);
        }
        $data = $query->first();
        $this->isCache && cache()->set($cache_key, $data, $this->expire);

        return $data;
    }

    /**
     * 获取分页数据
     * @param array $limit
     * @param array $where
     * @param array $order
     * @param array $field
     * @param int $refresh
     * @return mixed
     * Author: Jason<dcq@kuryun.cn>
     */
    public function getList($limit = [], $where = [], $order = [], $field = [], $refresh = 0)
    {
        ksort($where);
        ksort($order);
        ksort($field);
        $cache_key = md5($this->prefixCachekey() . __FUNCTION__ . serialize($limit) .serialize($where) . serialize($order) .serialize($field));
        if (cache()->has($cache_key)) {
            if (!$refresh) {
                return cache()->get($cache_key);
            }
            cache()->delete($cache_key);
        }
        $offset = ($limit[0] - 1) * $limit[1];
        $query = $this->whereBuilder($where)->offset($offset)->limit($limit[1]);
        if (!empty($field)) {
            $query = $query->select($field);
        }
        if(!empty($order)){
            $query = $this->_order($query, $order);
        }
        $data = $query->get();
        $this->isCache && cache()->set($cache_key, $data, $this->expire);

        return $data;
    }

    /**
     * 构建查询条件
     * @param array $where
     * [
     *      'id' => 1,
     *      'field1' => ['like', '%ddd%'],
     *      'field2' => ['or', ['>=', 0]],
     *      'field3' => ['in', [1, 2, 3]],
     *      'field4' => ['notin', [1, 2, 3]],
     *      'field5' => ['between', [1, 3]],
     *      'field6' => ['notbetween', [1, 3]],
     *      'field7' => ['>=', 1]
     *      ...
     * ]
     * @return mixed
     * Author: Jason<dcq@kuryun.cn>
     */
    private function whereBuilder($where = [])
    {
        $query = $this->model->query();
        if (!empty($where)) {
            foreach ($where as $field => $value) {
                if (is_array($value)) {
                    list($key, $item) = $value;
                    switch (strtolower((string)$key)) {
                        case 'like':
                            $arr = explode('|', $field);
                            $query->where(function ($query) use($arr, $item) {
                                foreach ($arr as $k => $f){
                                    if($k === 0){
                                        $query->where($f, 'like', $item);
                                    }else{
                                        $query->orWhere($f, 'like', $item);
                                    }
                                }
                            });
                            break;
                        case 'in':
                            $query->whereIn($field, $item);
                            break;
                        case 'notin':
                            $query->whereNotIn($field, $item);
                            break;
                        case 'between':
                            $query->whereBetween($field, $item);
                            break;
                        case 'notbetween':
                            $query->whereNotBetween($field, $item);
                            break;
                        case 'or':
                            $query->orWhere($field, $item[0], $item[1]);
                            break;
                        case 'neq':
                            $query->where($field, '<>', $item);
                            break;
                        case 'lt':
                            $query->where($field, '<', $item);
                            break;
                        case 'gt':
                            $query->where($field, '>', $item);
                            break;
                        case 'elt':
                            $query->where($field, '<=', $item);
                            break;
                        case 'egt':
                            $query->where($field, '>=', $item);
                            break;
                        default:
                            $query->where($field, $key, $item);
                    }
                } else {
                    $query->where($field, $value);
                }
            }
        }

        return $query;
    }

    /**
     * count统计
     * @param array $where
     * @param int $refresh
     * @return mixed
     * Author: Jason<dcq@kuryun.cn>
     */
    public function total($where = [], $refresh = 0)
    {
        ksort($where);
        $cache_key = md5($this->prefixCachekey() . __FUNCTION__ . serialize($where));
        if (cache()->has($cache_key)) {
            if (!$refresh) {
                return cache()->get($cache_key);
            }
            cache()->delete($cache_key);
        }
        $total = $this->whereBuilder($where)->count();
        $this->isCache && cache()->set($cache_key, $total, $this->expire);

        return $total;
    }

    /**
     * 根据条件获取数据
     * @param array $where
     * @param array $order
     * @param array $field
     * @param int $refresh
     * @return mixed
     * Author: Jason<dcq@kuryun.cn>
     */
    public function getAll($where = [], $order = [], $field = [], $refresh = 0)
    {
        $field = empty($field) ? [] : $this->_field($field);
        ksort($where);
        ksort($order);

        $cache_key = md5($this->prefixCachekey() . __FUNCTION__ . serialize($where) . serialize($order) . serialize($field));
        if (cache()->has($cache_key)) {
            if (!$refresh) {
                return cache()->get($cache_key);
            }
            cache()->delete($cache_key);
        }
        $query = $this->whereBuilder($where);
        if (!empty($field)) {
            $query = $query->select($field);
        }
        if(!empty($order)){
            $query = $this->_order($query, $order);
        }
        $data = $query->get();
        $this->isCache && cache()->set($cache_key, $data, $this->expire);

        return $data;
    }

    /**
     * 整理order参数
     * @param $query
     * @param $order
     * @return mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    private function _order($query, $order){
        $order_arr = [];
        if(! empty($order)){
            foreach ($order as $k => $v){
                if(is_int($k)){
                    if(is_array($v)){
                        array_push($order_arr, $v); //[['sort', 'desc'], ['id', 'asc']]
                    }else{
                        array_push($order_arr, $order); //['sort', 'desc']
                        break;
                    }
                }else{
                    array_push($order_arr, [$k, $v]); //['sort' => 'desc', 'id' => 'asc']
                }
            }
        }
        foreach ($order_arr as $v){
            $query = $query->orderBy(...$v);
        }
        return $query;
    }

    /**
     * 获取字段
     * @param array|string $field
     * @param array $where
     * @param int $refresh
     * @return array
     * Author: Jason<dcq@kuryun.cn>
     */
    public function getField($field = [], $where = [], $refresh = 0)
    {
        $field = $this->_field($field);
        $cache_key = md5($this->prefixCachekey() . __FUNCTION__ . serialize($where) . '::' . serialize($field));
        if (cache()->has($cache_key)) {
            if (!$refresh) {
                return cache()->get($cache_key);
            }
            cache()->delete($cache_key);
        }

        $data = $this->whereBuilder($where)->pluck(...$field)->toArray();
        $this->isCache && cache()->set($cache_key, $data, $this->expire);

        return $data;
    }

    /**
     * 整理字段
     * @param $field
     * @return array
     * Author: fudaoji<fdj@kuryun.cn>
     */
    private function _field($field){
        if(!is_array($field)){
            $field = explode(',', $field);
        }
        ksort($field);
        return $field;
    }

    /**
     * 根据某个字段不重复获取数据
     * @param array $field
     * @param array $where
     * @param int $refresh
     * @return array
     * Author: Jason<dcq@kuryun.cn>
     */
    public function distinctField($field = [], $where = [], $refresh = 0)
    {
        $cache_key = md5($this->prefixCachekey() . '::distinctField::' . serialize($where) . '::' . serialize($field));
        if (cache()->has($cache_key)) {
            if (!$refresh) {
                return cache()->get($cache_key);
            }
            cache()->delete($cache_key);
        }
        $data = $this->whereBuilder($where)->distinct()->pluck(...$field)->toArray();

        $this->isCache && cache()->set($cache_key, $data, $this->expire);

        return $data;
    }

    /**
     * sum求和
     * @param string $field
     * @param array $where
     * @param int $refresh
     * @return int $data
     * Author: Jason<dcq@kuryun.cn>
     */
    public function sums($field = '', $where = [], $refresh = 0)
    {
        $cache_key = md5($this->prefixCachekey() . __FUNCTION__ . serialize($where) . $field);
        if (cache()->has($cache_key)) {
            if (!$refresh) {
                return cache()->get($cache_key);
            }
            cache()->delete($cache_key);
        }
        $sums = $this->whereBuilder($where)->sum($field);
        $this->isCache && cache()->set($cache_key, $sums, $this->expire);

        return $sums;
    }

    /**
     * 自增或自减一个字段的值
     * @param int $type 1自增 2自减
     * @param array $where 查询条件
     * @param array $data [字段, 数字]
     * @return mixed
     * Author: Jason<dcq@kuryun.cn>
     */
    public function setIncDec($type = 1, $where = [], $data = [])
    {
        $query = $this->whereBuilder($where);
        $func = $type == 1 ? 'increment' : 'decrement';
        return $query->$func($data[0], $data[1]);
    }

    /**
     * 分组查询条件构建
     * @param object $query
     * @param array $having
     * [
     *      'field1' => ['>=', 1]
     *      ...
     * ]
     * @return mixed
     * Author: Jason<dcq@kuryun.cn>
     */
    private function havingBuilder(&$query, $having = [])
    {
        if (!empty($having)) {
            foreach ($having as $field => $value) {
                list($key, $item) = $value;
                $query->having($field, $key, $item);
            }
        }

        return $query;
    }

    /**
     * 获取group by结果分页列表
     * @param array $limit 分页参数
     * @param array $where 查询条件
     * @param array $group_field 分组字段
     * @param array $having 聚合条件
     * @param array $order 排序规则
     * @param array $field 需要查询字段
     * @param int $refresh 是否刷新缓存
     * @return mixed
     * Author: Jason<dcq@kuryun.cn>
     */
    public function getGroupList($limit = [], $where = [], $group_field = [], $having = [], $order = [], $field = [], $refresh = 0)
    {
        $cache_key = md5($this->prefixCachekey() . '::getGroupList::' . serialize($limit) . '::' . serialize($where) . '::' . serialize($group_field) . '::' . serialize($having) . '::' . serialize($order));
        if (cache()->has($cache_key)) {
            if (!$refresh) {
                return cache()->get($cache_key);
            }
            cache()->delete($cache_key);
        }
        $offset = ($limit[0] - 1) * $limit[1];
        $query = $this->whereBuilder($where)->groupBy($group_field);
        $query = $this->havingBuilder($query, $having)->orderBy(...$order)->offset($offset)->limit($limit[1]);
        if (!empty($field)) {
            $query = $query->select($field);
        }
        $data = $query->get();
        $this->isCache && cache()->set($cache_key, $data, $this->expire);

        return $data;
    }

    /**
     * 获取group by结果所有列表
     * @param array $where 查询条件
     * @param array $group_field 分组字段
     * @param array $having 聚合条件
     * @param array $order 排序规则
     * @param array $field 需要查询字段
     * @param int $refresh 是否刷新缓存
     * @return mixed
     * Author: Jason<dcq@kuryun.cn>
     */
    public function getGroupAll($where = [], $group_field = [], $having = [], $order = [], $field = [], $refresh = 0)
    {
        $cache_key = md5($this->prefixCachekey() . '::getGroupList::' . serialize($where) . '::' . serialize($group_field) . '::' . serialize($having) . '::' . serialize($order));
        if (cache()->has($cache_key)) {
            if (!$refresh) {
                return cache()->get($cache_key);
            }
            cache()->delete($cache_key);
        }
        $query = $this->whereBuilder($where)->groupBy($group_field);
        $query = $this->havingBuilder($query, $having)->orderBy(...$order);
        if (!empty($field)) {
            $query = $query->select($field);
        }
        $data = $query->get();
        $this->isCache && cache()->set($cache_key, $data, $this->expire);

        return $data;
    }

    /**
     * 获取有联合查询的分页数据
     * @param array $params
     * @return mixed
     * e.g: $this->dao->getListJoin([
     * 'join' => [['user as u', 'dao.user_id=u.id', 'inner|left|right']],
     * 'limit' => [1, 100],
     * 'where' => ['dao.id' => ['gt', 300]],
     * 'field' => ['u.username', 'dao.id as activity_id'],
     * 'order' => ['dao.id', 'desc']
     * ]);
     * Author: Jason<dcq@kuryun.cn>
     */
    public function getListJoin($params = []){
        ksort($params);
        $limit = $params['limit'];
        $where = empty($params['where']) ? [] : $params['where'];
        $order = empty($params['order']) ? [] : $params['order'];
        $field = empty($params['field']) ? true : $this->_field($params['field']);
        $refresh = empty($params['refresh']) ? 0 : $params['refresh'];
        unset($params['refresh']);
        $cache_key = md5($this->prefixCachekey() . __FUNCTION__ . serialize($params));
        if (cache()->has($cache_key)) {
            if (!$refresh) {
                return cache()->get($cache_key);
            }
            cache()->delete($cache_key);
        }
        $query = $this->whereBuilder($where);
        $this->_join($query, $params['join']);
        $offset = ($limit[0] - 1) * $limit[1];
        $query = $query->offset($offset)->limit($limit[1]);
        if (!empty($field)) {
            $query = $query->select($field);
        }
        if(!empty($order)){
            $query = $this->_order($query, $order);
        }
        $data = $query->get();
        $this->isCache && cache()->set($cache_key, $data, $this->expire);
        return $data;
    }

    /**
     * 获取关联查询所有数据
     * @param array $params
     * @return mixed
     * e.g: $this->dao->getAllJoin([
     * 'left_join' => [['user as u', 'dao.user_id', '=', 'u.id']],
     * 'where' => ['dao.id' => ['gt', 300]],
     * 'field' => ['u.username', 'dao.id as activity_id'],
     * 'order' => ['dao.id', 'desc']
     * ]);
     * Author Jason<dcq@kuryun.cn>
     */
    public function getAllJoin($params = []) {
        $where = empty($params['where']) ? [] : $params['where'];
        $order = empty($params['order']) ? [] : $params['order'];
        $field = empty($params['field']) ? true : $this->_field($params['field']);
        $refresh = empty($params['refresh']) ? 0 : $params['refresh'];
        unset($params['refresh']);
        $cache_key = md5($this->prefixCachekey() . '::getAllJoin::' . serialize($params));
        if (cache()->has($cache_key)) {
            if (!$refresh) {
                return cache()->get($cache_key);
            }
            cache()->delete($cache_key);
        }
        $query = $this->whereBuilder($where);
        $this->_join($query, $params['join']);
        if(!empty($order)){
            $query = $this->_order($query, $order);
        }
        if (!empty($field)) {
            $query = $query->select($field);
        }
        $data = $query->get();
        $this->isCache && cache()->set($cache_key, $data, $this->expire);
        return $data;
    }

    /**
     * 获取多表关联统计数据
     * @param array $params
     * @return mixed
     * e.g: $this->dao->totalJoin([
     * 'left_join' => [['user as u', 'dao.user_id', '=', 'u.id']],
     * 'where' => ['dao.id' => ['gt', 300]],
     * 'refresh' => 1
     * ]);
     * Author Jason<dcq@kuryun.cn>
     */
    public function totalJoin($params = []){
        ksort($params);
        $where = empty($params['where']) ? [] : $params['where'];
        $refresh = empty($params['refresh']) ? 0 : $params['refresh'];
        unset($params['refresh']);
        $cache_key = md5($this->prefixCachekey() . __FUNCTION__ . serialize($params));
        if (cache()->has($cache_key)) {
            if (!$refresh) {
                return cache()->get($cache_key);
            }
            cache()->delete($cache_key);
        }
        $query = $this->whereBuilder($where);
        $this->_join($query, $params['join']);
        $total = $query->count();
        $this->isCache && cache()->set($cache_key, $total, $this->expire);
        return $total;
    }

    private function _join(&$query, $params = []){
        foreach ($params as $k => $v){
            if(! is_array($v)){
                $v = $params;
                switch ($v[count($v) - 1]){ //['table', 'key1', '=', 'key2', 'inner']
                    case 'left':
                        $query->leftJoin($v[0], $v[1], $v[2], $v[3]);
                        break;
                    case 'right':
                        $query->rightJoin($v[0], $v[1], $v[2], $v[3]);
                        break;
                    case 'inner':
                    default:
                        $query->join($v[0], $v[1], $v[2], $v[3]);

                }
                break;
            }else{
                if(count($v) <= 3){ //['table', 'key1=key2', 'inner']
                    $join_where = explode('=', $v[1]);
                    array_splice($v, 1, 0, [$join_where[0], '=' ,$join_where[1]]);
                }
                $this->_join($query, $v);
            }
        }
    }

    /**
     * 获取单条数据的关联查询
     * @param $params array
     * @return mixed
     * e.g: $this->dao->getOneJoin([
     *          'join' => [
     *              ['user as u', 'dao.user_id=u.id', 'inner']
     *          ],
     *          'where' => ['dao.id' => ['gt', 300]],
     *          'field' => ['u.id'],
     *          'order' => ['u.id' => 'desc'],
     *          'refresh' => 1
     *      ]);
     * Author: Jason<dcq@kuryun.cn>
     */
    public function getOneJoin($params) {
        $where = empty($params['where']) ? [] : $params['where'];
        $refresh = empty($params['refresh']) ? 0 : $params['refresh'];
        $order = empty($params['order']) ? [] : $params['order'];
        $field = empty($params['field']) ? true : $this->_field($params['field']);
        unset($params['refresh']);
        $cache_key = md5($this->prefixCachekey() . __FUNCTION__ . serialize($params));
        if (cache()->has($cache_key)) {
            if (!$refresh) {
                return cache()->get($cache_key);
            }
            cache()->delete($cache_key);
        }
        $query = $this->whereBuilder($where);
        $this->_join($query, $params['join']);
        if (!empty($field)) {
            $query = $query->select($field);
        }
        if(!empty($order)){
            $query = $this->_order($query, $order);
        }
        $data = $query->first();
        $this->isCache && cache()->set($cache_key, $data, $this->expire);
        return $data;
    }
}