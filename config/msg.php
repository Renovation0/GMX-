<?php

/**
 * 返回值格式
 */

//定义返回值字母格式     基础1000-1999，  用户：2000-2999 商品：3000-3999， 订单：4000-4999 活动：5000-5999,
//基础变量定义
define('SUCCESS', '1');
define('SUCCESS_IN', '10');
define('SUCCESS_ZH', '100');
define('ERROT', '2');
define('ADD_FAIL','-1000');
define('ADD_FAIL_IN','-10000');
define('ADD_FAIL_ZH','-100000');
define('UPDATA_FAIL','-1001');
define('DELETE_FAIL','-1002');
define('DELETE_FAIL_IN','-10020');
define('DELETE_FAIL_ZH','-100200');
define('SYSTEM_DELETE_FAIL','-1003');
define('WEIXIN_AUTH_ERROR', '-1004');
define('NO_AITHORITY', '-1005');
define('NO_AITHORITY_IN', '-10050');
define('NO_AITHORITY_ZH', '-100500');
define('PARAMETER_ERROR','-1006');
define('PARAMETER_ERROR_IN','-10060');
define('PARAMETER_ERROR_ZH','-100600');
define('UPLOAD_FAIL','-1007');
define('MISS_FAIL','-1008');
define('MISS_FAIL_IN','-10080');
define('MISS_FAIL_ZH','-100800');

//用户变量定义
define('LOGIN_FAIL','-2000');
define('LOGIN_FAIL_IN','-20000');
define('LOGIN_FAIL_ZH','-200000');
define('USER_ERROR', '-2001');
define('USER_LOCK', '-2002');
define('USER_LOCK_IN', '-20020');
define('USER_LOCK_ZH', '-200200');
define('USER_NBUND', '-2003');
define('USER_NBUND_IN', '-20030');
define('USER_NBUND_ZH', '-200300');
define('USER_REPEAT', '-2004');
define('USER_REPEAT_IN', '-20040');
define('USER_REPEAT_ZH', '-200400');
define('PASSWORD_ERROR', '-2005');
define('PASSWORD_ERROR_IN', '-20050');
define('PASSWORD_ERROR_ZH', '-200500');
define('USER_SH', '-2095');
define('USER_WORDS_ERROR', '-2006');
define('USER_WORDS_ERROR_IN', '-20060');
define('USER_WORDS_ERROR_ZH', '-200600');
define('USER_ADDRESS_DELETE_ERROR', '-2007');
define('USER_GROUP_ISUSE', '-2008');
define('NO_LOGIN','-2009');
define('USER_HEAD_GET','-2010');
define('USER_HEAD_GET_IN','-20100');
define('USER_HEAD_GET_ZH','-201000');
define('NO_COUPON','-2011');
define('NO_COUPON_IN','-20111');
define('NO_COUPON_ZH','-201110');
define('USER_MOBILE_REPEAT', '-2012');
define('USER_MOBILE_REPEAT_IN', '-20121');
define('USER_MOBILE_REPEAT_ZH', '-201210');
define('USER_EMAIL_REPEAT', '-2013');
define('USER_GROUP_REPEAT', '-2014');
define('USER_WITHDRAW_NO_USE', '-2015');
define('USER_WITHDRAW_NO_USE_IN', '-20151');
define('USER_WITHDRAW_NO_USE_ZH', '-201510');
define('USER_WITHDRAW_BEISHU', '-2016');
define('USER_WITHDRAW_MIN', '-2017');
define('MEMBER_LEVEL_DELETE', '-2018');
define('SMS_TYPE_EERROR', '-2019');
define('NULL_PHONE',  '2020');
define('NULL_PHONE_IN',  '20200');
define('NULL_PHONE_ZH',  '202000');
define('REGISTER_PHONE', '-2021');
define('REGISTER_PHONE_IN', '-20210');
define('REGISTER_PHONE_ZH', '-202100');
define('SENDSMS_FAIL', '-2022');
define('SENDSMS_FAIL_IN', '-20220');
define('SENDSMS_FAIL_ZH', '-202200');
define('REGISTER_USER_FAIL', '-2023');
define('REGISTER_USER_FAIL_IN', '-20230');
define('REGISTER_USER_FAIL_ZH', '-202300');
define('STEP_CHECK_FAIL', '-2024');
define('REGISTER_STEP_FAIL', '-2025');
define('TOKEN_VERIFY_FAIL', '-2026');
define('VERIFY_OODE_INVALID', '-2027');
define('VERIFY_OODE_ERROR', '-2028');
define('VERIFY_OODE_ERROR_IN', '-20280');
define('VERIFY_OODE_ERROR_ZH', '-202800');
define('NULL_CODE', '-2029');
define('NULL_CODE_IN', '-20290');
define('NULL_PASSWORD', '-2030');
define('NULL_PASSWORD_IN', '-20300');
define('NULL_PASSWORD_ZH', '-203000');
define('EDIT_PASSWORD_FAIL', '-2031');
define('NO_REGISTER_PHONE', '-2032');
define('NO_REGISTER_PHONE_IN', '-20320');
define('NO_REGISTER_PHONE_ZH', '-203200');
define('NOFOUND_PROVINCE', '-2033');
define('NOFOUND_CITY', '-2034');
define('NOFOUND_AREA', '-2035');
define('EDIT_PAY_PASSWORD_FAIL','-2036');
define('NO_INFO', '-2037');
define('EDIT_NICKNAME_FAIL', '-2038');
define('EDIT_REALNAME_FAIL', '-2039');
define('EDIT_PHONE_FAIL', '-2040');
define('IS_SETTED', '-2041');
define('NO_ORDER', '-2042');
define('ORDER_REMIND_FAIL', '-2043');
define('ORDER_CANCEL_FAIL', '-2044');
define('ORDER_CONFIRM_FAIL', '-2045');
define('ORDER_RETURN_FAIL', '-2046');
define('ORDER_EVALUATE_FAIL', '-2047');
define('USER_BLANCE_NO', '-2048');
define('USER_BLANCE_NO_IN', '-20481');
define('USER_BLANCE_NO_ZH', '-204810');
define('ORDER_DELETE_FAIL', '-2049');
define('ORDER_LOOKEVALUATE_FAIL', '-2050');
define('ORDER_LOOKRETURN_FAIL', '-2051');
define('ORDER_CONFIRMRETURN_FAIL', '-2052');
define('ORDER_REFUSERETURN_FAIL', '-2053');
define('ORDER_ADRESSEXPRESS_FAIL', '-2054');
define('ORDER_DELIVER_FAIL', '-2055');
define('NO_CARD_ERROR', '-2056');
define('ORDER_LOOKEXPRESS_FAIL', '-2057');
define('SHOP_ID_FAIL', '-2058');
define('WITHDRAW_FAIL', '-2059');
define('ORDER_LOOKINFO_FAIL', '-2060');
define('NO_USER_INFO','-2061');
define('NULL_USERNAME', '-2062');
define('NO_BANK', '-2063');
define('AGENT_ALRE_EXISTED', '-2064');
define('USER_AGENT_ALRE_EXISTED', '-2065');
define('USER_LEVEL_AGENT_NO','-2066');
define('BANNA_FAIL','-2067');
define('SHOP_FIVE','-2068');
define('BANK_FIVE','-2069');
define('ORDER_SUBMIT_FAIL','-2070');
define('LOGIN_AGAIN', '-2071');
define('LOGIN_AGAIN_IN', '-20710');
define('ISSET_SHOPNAME', '-2072');
define('WITHDRAW_ADD_FAIL','-2073');
define('USERNAME_FAIL', '-2074');
define('ORDER_CONFIRMORDER_FAIL','-2075');
define('WIT_MONEY', '-2076');
define('NO_USER_VIP_LEVEL', '-2077');
define('MIN_MONEY', '-2078');
define('ALL_SHARE_FAIL','-2079');
define('AGENT_SHARE_FAIL','-2080');
define('SHOP_USERL_FAIL','-2081');
define('BANK_DELEFE','-2082');
define('PWD_MIN_LEN','-2083');
define('PWD_MAX_LEN','-2084');
define('USER_NAME_MAX','-2085');
define('USER_FIAL','-2086');
define('USER_FIAL_IN','-20860');
define('USER_FIAL_ZH','-208600');
define('SONCATEGORY_FIAL','-2087');
define('ISSET_CATEGORY','-2088');
define('IS_COLLECTION_FAIL','-2089');
define('UPLOAD_PICTURE','-2999');
define('NO_RECOMMENDER','-3000');
define('NO_RECOMMENDER_IN','-30000');
define('NO_RECOMMENDER_ZH','-300000');
define('IP_LIMIT','-3001');
define('IP_LIMIT_IN','-30010');
define('IP_LIMIT_ZH','-300100');
define('PASSWORD_FAIL','-3002');
define('PASSWORD_FAIL_IN','-30020');
define('PASSWORD_FAIL_ZH','-300200');
define('USER_NOMO','-3005');
define('USER_NOMO_IN','-30050');
define('USER_NOMO_ZH','-300500');
define('USER_VIP_ISUSE','-3006');
define('USER_VIP_ISUSE_IN','-30060');
define('USER_VIP_ISUSE_ZH','-300600');
define('NOT_OPERATE','-3006');
define('NOT_OPERATE_IN','-30060');
define('NOT_OPERATE_ZH','-300600');



function getErrorInfo($error_code)
{
    $system_error_arr = array(
        //基础变量
        SUCCESS  => 'Success',
        SUCCESS_IN  => 'सफल',
        ADD_FAIL => 'Add fail',
        ADD_FAIL_IN => 'जोड़ें असफल',
        UPDATA_FAIL => 'Update fail',
        DELETE_FAIL => 'Delete fail ',
        DELETE_FAIL_IN => 'मिटाने विफल ',
        SYSTEM_DELETE_FAIL => '当前模块下存在子模块,不能删除!',
        NO_AITHORITY => 'Illegal operation', //非法操作
        NO_AITHORITY_IN => 'अवैध प्रक्रिया', //非法操作
        PARAMETER_ERROR => 'Parameter error', //参数错误
        PARAMETER_ERROR_IN => 'पैरामीटर त्रुटि', //参数错误
        UPLOAD_FAIL=>'Upload fail',
        MISS_FAIL=>'Missing required parameters', //缺少必要参数
        MISS_FAIL_IN=>'आवश्यक पैरामीटर्स गुम है', //缺少必要参数
        MISS_FAIL_ZH=>'缺少必要参数', //缺少必要参数
        NOT_OPERATE=>'Do not operate frequently', //请勿频繁操作
        NOT_OPERATE_IN=>'बहुत से काम नहीं करें', //请勿频繁操作
        //用户变量定义
        LOGIN_FAIL => 'Login fail',
        LOGIN_FAIL_IN => 'लॉगइन असफल',
        USER_ERROR => 'Account error',
        USER_LOCK  => 'User locked',//'用户被锁定',
        USER_LOCK_IN  => 'प्रयोक्ता ताला लगाया गया',//'用户被锁定',
        USER_NBUND => 'The user does not exist',//未找到用户
        USER_NBUND_IN => 'उपयोक्ता मौजूद नहीं है',//未找到用户
        USER_REPEAT => 'User name already exists',//用户名已存在
        USER_REPEAT_IN => 'प्रयोक्ता नाम पहले से ही मौजूद है',//密码错误
        PASSWORD_ERROR => 'Password error',
        PASSWORD_ERROR_IN => 'पासवर्ड त्रुटि',
        USER_WORDS_ERROR => 'No extraction conditions were reached',//没有达到提取条件
        USER_WORDS_ERROR_IN => 'कोई निकाला परिस्थिति नहीं पहुँचा गया',//没有达到提取条件
        USER_WORDS_ERROR_ZH => '没有达到提取条件',//没有达到提取条件
        LOGIN_AGAIN => 'Your credentials have expired. Please log in again',//凭据已过期，请重新登录
        LOGIN_AGAIN_IN => 'आपके प्रमाणपत्र मियाद समाप्त है. कृपया फिर लॉग इन करें',//凭据已过期，请重新登录
        USER_VIP_ISUSE => 'You have not reached this VIP level',//您还未到当前VIP等级
        USER_VIP_ISUSE_IN => 'आप इस VIP स्तर पर नहीं पहुँचेl',//您还未到当前VIP等级
        NO_LOGIN => '当前用户未登录',
        USER_HEAD_GET => 'The maximum number of purchases has been exceeded',//已超过最大购买次数
        USER_HEAD_GET_IN => 'क्रियाओं की अधिकतम संख्या बढ़ी गयी है',//已超过最大购买次数
        NO_COUPON => 'please wait for open registration',//请等待开放注册
        NO_COUPON_IN => 'कृपया रिजिस्ट्रेशन खोले के लिए इंतजार करें',//请等待开放注册
        NO_RECOMMENDER => 'recommender does not exist ',//推荐人不存在
        NO_RECOMMENDER_IN => 'सिफारिस कर्ता मौजूद नहीं है ',//推荐人不存在
        NO_RECOMMENDER_ZH => '推荐人不存在',//推荐人不存在
        USER_MOBILE_REPEAT => 'Duplicate mobile phone number',//手机号码重复
        USER_MOBILE_REPEAT_IN => 'मोबाइल फोन संख्या नक्कल करें',//手机号码重复
        IP_LIMIT => 'the IP registered user has exceeded the limit',//该IP注册用户已超过限制
        IP_LIMIT_IN => 'आईपी रेजिस्टरेड प्रयोक्ता ने सीमा से बढ़ाया है',//该IP注册用户已超过限制
        USER_EMAIL_REPEAT =>'用户邮箱重复',
        USER_GROUP_REPEAT => '用户组名称重复',
        USER_WITHDRAW_NO_USE => 'Withdrawal function has not been opened yet',//提现功能未开放
        USER_WITHDRAW_NO_USE_IN => 'फंक्शन विच्छेदवाल अभी नहीं खोला गया है',//提现功能未开放
        USER_WITHDRAW_BEISHU => '提现倍数不符合',
        USER_WITHDRAW_MIN    => '申请提现小于最低提现',
        USER_BLANCE_NO       => 'Sorry, your credit is running low',//余额不足
        USER_BLANCE_NO_IN       => 'माफ़ करें, आपका क्रेडिट कम चल रहा है',//余额不足
        MEMBER_LEVEL_DELETE    => '该等级正在使用中,不可删除',
        SMS_TYPE_EERROR => '短信类型不正确',
        NULL_PHONE => 'Mobile phone number cannot be empty',//手机号码不能为空
        NULL_PHONE_IN => 'मोबाइल फोन संख्या खाली नहीं हो सकता',//手机号码不能为空
        REGISTER_PHONE => 'The phone is already registered',//该号码已注册
        REGISTER_PHONE_IN => 'फोन पहले से ही रेजिस्टर किया गया है',//该号码已注册
        SENDSMS_FAIL =>'Failed to send verification code. Please try again later',//短信验证码发送失败，请稍后再试
        SENDSMS_FAIL_IN =>'सत्यापन कोड भेजने में विफल. कृपया बाद फिर कोशिश करें',//短信验证码发送失败，请稍后再试
        REGISTER_USER_FAIL =>'Failed to register user',//注册失败
        REGISTER_USER_FAIL_IN =>'प्रयोक्ता रेजिस्टर करने में असफल',//注册失败
        STEP_CHECK_FAIL =>'上一步验证失败',
        REGISTER_STEP_FAIL =>'注册步骤失败',
        LOGIN_AGAIN =>'Please login again',//重新登录
        LOGIN_AGAIN_IN =>'कृपया फिर लागइन करें',//重新登录
        TOKEN_VERIFY_FAIL =>'token错误验证失败',
        VERIFY_OODE_INVALID =>'验证码失效,请重新获取',
        VERIFY_OODE_ERROR =>'Verification code input error, please re-enter',//验证码输入错误，重新输入
        VERIFY_OODE_ERROR_IN =>'प्रमाणीकरण कोड इनपुट त्रुटि, कृपया फिर प्रविष्ट करें',//验证码输入错误，重新输入
        NULL_CODE =>'Verification code cannot be empty',//验证码不能为空
        NULL_CODE_IN =>'प्रमाणीकरण कोड खाली नहीं हो सकता',//验证码不能为空
        NULL_PASSWORD =>'Password cannot be empty',//'密码不能为空',
        NULL_PASSWORD_IN =>'पासवर्ड खाली नहीं हो सकता',//'密码不能为空',
        PASSWORD_FAIL =>'Password error',//'密码错误',
        EDIT_PASSWORD_FAIL =>'修改密码失败',
        NO_REGISTER_PHONE => 'Invitation code cannot be empty',//'邀请码不能为空',
        NO_REGISTER_PHONE_IN => 'निमन्त्रण कोड खाली नहीं हो सकता',//'邀请码不能为空',
        NO_USER_INFO => '会员信息获取失败',
        NOFOUND_PROVINCE => '未查到省',
        NOFOUND_CITY => '未查到市',
        NOFOUND_AREA => '未查到区',
        EDIT_PAY_PASSWORD_FAIL => '修改支付密码失败',
        NO_INFO => '暂无消息',
        EDIT_NICKNAME_FAIL=>'修改昵称失败',
        EDIT_REALNAME_FAIL=>'修改姓名失败',
        EDIT_PHONE_FAIL=>'修改手机失败',
        IS_SETTED => '店铺已入驻',
        NO_ORDER => '暂无此类订单',
        ORDER_REMIND_FAIL=>'提醒发货失败',
        ORDER_CANCEL_FAIL=>'取消订单失败',
        ORDER_CONFIRM_FAIL=>'确认收货失败',
        ORDER_RETURN_FAIL=>'申请退换货失败',
        ORDER_EVALUATE_FAIL=>'订单评价失败',
        ORDER_DELETE_FAIL=>'订单删除失败',
        NO_CARD_ERROR=>'充值卡不存在或已经使用',
        ORDER_LOOKEVALUATE_FAIL=>'查看订单评价失败',
        ORDER_LOOKRETURN_FAIL=>'查看退货理由失败',
        ORDER_CONFIRMRETURN_FAIL=>'同意退货失败',
        ORDER_REFUSERETURN_FAIL=>'拒绝退货失败',
        ORDER_ADRESSEXPRESS_FAIL=>'订单地址及快递物流查询失败',
        ORDER_DELIVER_FAIL=>'发货失败',
        ORDER_LOOKEXPRESS_FAIL=>'查看快递物流失败',
        SHOP_ID_FAIL=>'获取店铺失败',
        WITHDRAW_FAIL=>'余额不足',
        NULL_USERNAME => '用户名不能为空',
        ORDER_LOOKINFO_FAIL=>'查看订单内容失败',
        NO_BANK=>'请选择或添加银行卡',
        AGENT_ALRE_EXISTED =>'该区域代理商已被申请,请申请其它区域!',
        USER_AGENT_ALRE_EXISTED => '你已经是会员代理，不可再申请！',
        USER_LEVEL_AGENT_NO => '铂钻会员才可申请会员区域代理！',
        BANNA_FAIL=>'广告位查询失败',
        SHOP_FIVE=>'地址最多只可创建5个',
        BANK_FIVE=>'绑定银行卡最多只可创建5个',
        ORDER_SUBMIT_FAIL=>'订单提交失败',
        ISSET_SHOPNAME=>'店铺名已存在',
        WITHDRAW_ADD_FAIL=>'比例相加须等于100',
        USERNAME_FAIL=>'用户名不存在',
        ORDER_CONFIRMORDER_FAIL=>'确认订单失败',
        WIT_MONEY=>'提现金额需大于0.1！',
        NO_USER_VIP_LEVEL=>'暂无升级选项',
        MIN_MONEY=>'提现额需大于最低提现金额',
        ALL_SHARE_FAIL=>'总和需小于等于100',
        AGENT_SHARE_FAIL=>'总和需小于区域代理所占比例值',
        SHOP_USERL_FAIL=>'申请店铺入驻需提升会员等级至金牌会员及以上',
        BANK_DELEFE=>'改银行卡已被用户删除',
        PWD_MIN_LEN=>'密码设置需大于3位',
        PWD_MAX_LEN=>'密码设置需小于18位',
        USER_NAME_MAX=>'用户名设置最多6个中文字符或者18位数字或字母字符',
        USER_FIAL=>'Account has been disabled',//'用户名被禁用',
        USER_FIAL_IN=>'खाता अक्षम किया गया है',//'用户名被禁用',
        USER_NOMO=>'Error, no user found',//'用户名被禁用',
        USER_NOMO_IN=>'त्रुटि, कोई प्रयोक्ता नहीं मिला',//'用户名被禁用',
       
        
    );
    if(array_key_exists($error_code, $system_error_arr))
    {
        return $system_error_arr[$error_code];
    } elseif($error_code > 0){
        return '成功';
    }else{
        return '失败';
    }
}


function getErrorInfo_new($error_code,$lang)
{
    $system_error_arr = array(
        //基础变量
        SUCCESS  => 'Success',
        SUCCESS_IN  => 'सफल',
        SUCCESS_ZH  => '成功',
        ADD_FAIL => 'Add fail',
        ADD_FAIL_IN => 'जोड़ें असफल',
        ADD_FAIL_ZH => '失败',
        UPDATA_FAIL => 'Update fail',
        DELETE_FAIL => 'Delete fail ',
        DELETE_FAIL_IN => 'मिटाने विफल ',
        SYSTEM_DELETE_FAIL => '当前模块下存在子模块,不能删除!',
        NO_AITHORITY => 'Illegal operation', //非法操作
        NO_AITHORITY_IN => 'अवैध प्रक्रिया', //非法操作
        PARAMETER_ERROR => 'Parameter error', //参数错误
        PARAMETER_ERROR_IN => 'पैरामीटर त्रुटि', //参数错误
        PARAMETER_ERROR_ZH => '参数错误', //参数错误
        UPLOAD_FAIL=>'Upload fail',
        MISS_FAIL=>'Missing required parameters', //缺少必要参数
        MISS_FAIL_IN=>'आवश्यक पैरामीटर्स गुम है', //缺少必要参数
        MISS_FAIL_ZH=>'आवश्यक पैरामीटर्स गुम है', //缺少必要参数
        NOT_OPERATE=>'Do not operate frequently', //请勿频繁操作
        NOT_OPERATE_IN=>'बहुत से काम नहीं करें', //请勿频繁操作
        //用户变量定义
        LOGIN_FAIL => 'Login fail',
        LOGIN_FAIL_IN => 'लॉगइन असफल',
        LOGIN_FAIL_ZH => 'Login fail',
        USER_ERROR => 'Account error',
        USER_LOCK  => 'User locked',//'用户被锁定',
        USER_LOCK_IN  => 'प्रयोक्ता ताला लगाया गया',//'用户被锁定',
        USER_LOCK_ZH  => '用户被锁定',//'用户被锁定',
        USER_NBUND => 'The user does not exist',//未找到用户
        USER_NBUND_IN => 'उपयोक्ता मौजूद नहीं है',//未找到用户
        USER_NBUND_ZH => '用户不存在',//未找到用
        USER_REPEAT => 'User name already exists',//用户名已存在
        USER_REPEAT_IN => 'प्रयोक्ता नाम पहले से ही मौजूद है',//密码错误
        PASSWORD_ERROR => 'Password error',
        PASSWORD_ERROR_IN => 'पासवर्ड त्रुटि',
        USER_WORDS_ERROR => 'No extraction conditions were reached',//没有达到提取条件
        USER_WORDS_ERROR_IN => 'कोई निकाला परिस्थिति नहीं पहुँचा गया',//没有达到提取条件
        USER_WORDS_ERROR_ZH => '没有达到提取条件',//没有达到提取条件
        LOGIN_AGAIN => 'Your credentials have expired. Please log in again',//凭据已过期，请重新登录
        LOGIN_AGAIN_IN => 'आपके प्रमाणपत्र मियाद समाप्त है. कृपया फिर लॉग इन करें',//凭据已过期，请重新登录
        USER_VIP_ISUSE => 'You have not reached this VIP level',//您还未到当前VIP等级
        USER_VIP_ISUSE_IN => 'आप इस VIP स्तर पर नहीं पहुँचेl',//您还未到当前VIP等级
        USER_VIP_ISUSE_ZH => '您还未到当前VIP等级',//您还未到当前VIP等级
        NO_LOGIN => '当前用户未登录',
        USER_HEAD_GET => 'The maximum number of purchases has been exceeded',//已超过最大购买次数
        USER_HEAD_GET_IN => 'क्रियाओं की अधिकतम संख्या बढ़ी गयी है',//已超过最大购买次数
        USER_HEAD_GET_ZH => '已超过最大购买次数',//已超过最大购买次数
        NO_COUPON => 'please wait for open registration',//请等待开放注册
        NO_COUPON_IN => 'कृपया रिजिस्ट्रेशन खोले के लिए इंतजार करें',//请等待开放注册
        NO_RECOMMENDER => 'recommender does not exist ',//推荐人不存在
        NO_RECOMMENDER_IN => 'सिफारिस कर्ता मौजूद नहीं है ',//推荐人不存在
        NO_RECOMMENDER_ZH => '推荐人不存在',//推荐人不存在
        USER_MOBILE_REPEAT => 'Duplicate mobile phone number',//手机号码重复
        USER_MOBILE_REPEAT_IN => 'मोबाइल फोन संख्या नक्कल करें',//手机号码重复
        USER_MOBILE_REPEAT_ZH => '手机号码重复',//手机号码重复
        IP_LIMIT => 'the IP registered user has exceeded the limit',//该IP注册用户已超过限制
        IP_LIMIT_IN => 'आईपी रेजिस्टरेड प्रयोक्ता ने सीमा से बढ़ाया है',//该IP注册用户已超过限制
        IP_LIMIT_ZH => '该IP注册用户已超过限制',//该IP注册用户已超过限制
        USER_EMAIL_REPEAT =>'用户邮箱重复',
        USER_GROUP_REPEAT => '用户组名称重复',
        USER_WITHDRAW_NO_USE => 'Withdrawal function has not been opened yet',//提现功能未开放
        USER_WITHDRAW_NO_USE_IN => 'फंक्शन विच्छेदवाल अभी नहीं खोला गया है',//提现功能未开放
        USER_WITHDRAW_BEISHU => '提现倍数不符合',
        USER_WITHDRAW_MIN    => '申请提现小于最低提现',
        USER_BLANCE_NO       => 'Sorry, your credit is running low',//余额不足
        USER_BLANCE_NO_IN       => 'माफ़ करें, आपका क्रेडिट कम चल रहा है',//余额不足
        MEMBER_LEVEL_DELETE    => '该等级正在使用中,不可删除',
        SMS_TYPE_EERROR => '短信类型不正确',
        NULL_PHONE => 'Mobile phone number cannot be empty',//手机号码不能为空
        NULL_PHONE_IN => 'मोबाइल फोन संख्या खाली नहीं हो सकता',//手机号码不能为空
        REGISTER_PHONE => 'The phone is already registered',//该号码已注册
        REGISTER_PHONE_IN => 'फोन पहले से ही रेजिस्टर किया गया है',//该号码已注册
        SENDSMS_FAIL =>'Failed to send verification code. Please try again later',//短信验证码发送失败，请稍后再试
        SENDSMS_FAIL_IN =>'सत्यापन कोड भेजने में विफल. कृपया बाद फिर कोशिश करें',//短信验证码发送失败，请稍后再试
        REGISTER_USER_FAIL =>'Failed to register user',//注册失败
        REGISTER_USER_FAIL_IN =>'प्रयोक्ता रेजिस्टर करने में असफल',//注册失败
        STEP_CHECK_FAIL =>'上一步验证失败',
        REGISTER_STEP_FAIL =>'注册步骤失败',
        LOGIN_AGAIN =>'Please login again',//重新登录
        LOGIN_AGAIN_IN =>'कृपया फिर लागइन करें',//重新登录
        TOKEN_VERIFY_FAIL =>'token错误验证失败',
        VERIFY_OODE_INVALID =>'验证码失效,请重新获取',
        VERIFY_OODE_ERROR =>'Verification code input error, please re-enter',//验证码输入错误，重新输入
        VERIFY_OODE_ERROR_IN =>'प्रमाणीकरण कोड इनपुट त्रुटि, कृपया फिर प्रविष्ट करें',//验证码输入错误，重新输入
        NULL_CODE =>'Verification code cannot be empty',//验证码不能为空
        NULL_CODE_IN =>'प्रमाणीकरण कोड खाली नहीं हो सकता',//验证码不能为空
        NULL_PASSWORD =>'Password cannot be empty',//'密码不能为空',
        NULL_PASSWORD_IN =>'पासवर्ड खाली नहीं हो सकता',//'密码不能为空',
        PASSWORD_FAIL =>'Password error',//'密码错误',
        PASSWORD_FAIL_IN =>'Password error',//'密码错误',
        PASSWORD_FAIL_ZH =>'密码错误',//'密码错误',
        EDIT_PASSWORD_FAIL =>'修改密码失败',
        NO_REGISTER_PHONE => 'Invitation code cannot be empty',//'邀请码不能为空',
        NO_REGISTER_PHONE_IN => 'निमन्त्रण कोड खाली नहीं हो सकता',//'邀请码不能为空',
        NO_REGISTER_PHONE_ZH => '邀请码不能为空',//'邀请码不能为空',
        NO_USER_INFO => '会员信息获取失败',
        NOFOUND_PROVINCE => '未查到省',
        NOFOUND_CITY => '未查到市',
        NOFOUND_AREA => '未查到区',
        EDIT_PAY_PASSWORD_FAIL => '修改支付密码失败',
        NO_INFO => '暂无消息',
        EDIT_NICKNAME_FAIL=>'修改昵称失败',
        EDIT_REALNAME_FAIL=>'修改姓名失败',
        EDIT_PHONE_FAIL=>'修改手机失败',
        IS_SETTED => '店铺已入驻',
        NO_ORDER => '暂无此类订单',
        ORDER_REMIND_FAIL=>'提醒发货失败',
        ORDER_CANCEL_FAIL=>'取消订单失败',
        ORDER_CONFIRM_FAIL=>'确认收货失败',
        ORDER_RETURN_FAIL=>'申请退换货失败',
        ORDER_EVALUATE_FAIL=>'订单评价失败',
        ORDER_DELETE_FAIL=>'订单删除失败',
        NO_CARD_ERROR=>'充值卡不存在或已经使用',
        ORDER_LOOKEVALUATE_FAIL=>'查看订单评价失败',
        ORDER_LOOKRETURN_FAIL=>'查看退货理由失败',
        ORDER_CONFIRMRETURN_FAIL=>'同意退货失败',
        ORDER_REFUSERETURN_FAIL=>'拒绝退货失败',
        ORDER_ADRESSEXPRESS_FAIL=>'订单地址及快递物流查询失败',
        ORDER_DELIVER_FAIL=>'发货失败',
        ORDER_LOOKEXPRESS_FAIL=>'查看快递物流失败',
        SHOP_ID_FAIL=>'获取店铺失败',
        WITHDRAW_FAIL=>'余额不足',
        NULL_USERNAME => '用户名不能为空',
        ORDER_LOOKINFO_FAIL=>'查看订单内容失败',
        NO_BANK=>'请选择或添加银行卡',
        AGENT_ALRE_EXISTED =>'该区域代理商已被申请,请申请其它区域!',
        USER_AGENT_ALRE_EXISTED => '你已经是会员代理，不可再申请！',
        USER_LEVEL_AGENT_NO => '铂钻会员才可申请会员区域代理！',
        BANNA_FAIL=>'广告位查询失败',
        SHOP_FIVE=>'地址最多只可创建5个',
        BANK_FIVE=>'绑定银行卡最多只可创建5个',
        ORDER_SUBMIT_FAIL=>'订单提交失败',
        ISSET_SHOPNAME=>'店铺名已存在',
        WITHDRAW_ADD_FAIL=>'比例相加须等于100',
        USERNAME_FAIL=>'用户名不存在',
        ORDER_CONFIRMORDER_FAIL=>'确认订单失败',
        WIT_MONEY=>'提现金额需大于0.1！',
        NO_USER_VIP_LEVEL=>'暂无升级选项',
        MIN_MONEY=>'提现额需大于最低提现金额',
        ALL_SHARE_FAIL=>'总和需小于等于100',
        AGENT_SHARE_FAIL=>'总和需小于区域代理所占比例值',
        SHOP_USERL_FAIL=>'申请店铺入驻需提升会员等级至金牌会员及以上',
        BANK_DELEFE=>'改银行卡已被用户删除',
        PWD_MIN_LEN=>'密码设置需大于3位',
        PWD_MAX_LEN=>'密码设置需小于18位',
        USER_NAME_MAX=>'用户名设置最多6个中文字符或者18位数字或字母字符',
        USER_FIAL=>'Account has been disabled',//'用户名被禁用',
        USER_FIAL_IN=>'खाता अक्षम किया गया है',//'用户名被禁用',
        USER_NOMO=>'Error, no user found',//'用户名被禁用',
        USER_NOMO_IN=>'त्रुटि, कोई प्रयोक्ता नहीं मिला',//'用户名被禁用',
       
        
    );
    
    //处理code
    if($lang!="EN"){
		$error_code = $error_code.'_'.$lang;
	}
    $error_code = constant($error_code);
    if(array_key_exists($error_code, $system_error_arr))
    {
        return $system_error_arr[$error_code];
    } elseif($error_code > 0){
        return '成功';
    }else{
        return '失败';
    }
}

