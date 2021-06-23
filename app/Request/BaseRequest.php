<?php
/**
 * Created by PhpStorm.
 * Script Name: BaseRequest.php
 * Create: 2021/1/21 14:30
 * Description:
 * Author: fudaoji<fdj@kuryun.cn>
 */
declare(strict_types=1);
namespace App\Request;

use Hyperf\HttpServer\Request;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Di\Annotation\Inject;

abstract class BaseRequest
{
    protected $ruleMobile = 'required|regex:/^1[3-9]\d{9}$/';
    protected $ruleLagerOne = 'required|integer|min:1';
    protected $ruleRefresh = 'required|boolean';
    protected $ruleSmsCode = 'required|regex:/^\d{6}$/';
    protected $ruleTimestamp = 'required|digits:10';
    protected $ruleSex = 'required|in:0,1,2';
    protected $ruleName = 'required|between:1,20';
    protected $ruleAddress = 'required|between:1,200';
    protected $ruleEgtZero = 'required|integer|min:0';
    protected $ruleArray = 'required|array';
    protected $rules = [];

    /**
     * @Inject()
     * @var Request
     */
    private $request;

    abstract function setRules($fields = []);

    public function messages(): array
    {
        return [];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 暴露给外部调用的验证触发方法
     * @param string $scene
     * Author: fudaoji<fdj@kuryun.cn>
     * @return array
     */
    public function doValidate(string $scene='')
    {
        if($scene && method_exists($this, $action = 'scene' . ucfirst($scene))){
            call_user_func([$this, $action]);
        } else {
            $this->setRules();
        }
        $validator = di(ValidatorFactoryInterface::class)->make(
            $this->request->all(),
            $this->rules,
            $this->messages()
        );

        if ($validator->fails()){
            return ['code' => 0, 'msg' => $validator->errors()->first()];
        }else{
            return ['code' => 1, 'data' => $validator->validated()];
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return $this->rules;
    }

    /**
     * 列表刷新
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function sceneRefreshPage(){
        $fields = ['refresh','current_page', 'page_size'];
        $this->setRules($fields);
    }

    /**
     * 列表刷新
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function sceneDel(){
        $fields = ['id'];
        $this->setRules($fields);
    }

    /**
     * 只需要刷新参数
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function sceneRefresh(){
        $fields = ['refresh', 'xq_id'];
        $this->setRules($fields);
    }
}