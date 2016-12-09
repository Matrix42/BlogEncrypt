<?php
/**
 * Typecho 整站加密插件
 * 
 * @package BlogEncrypt
 * @author Matrix42
 * @version 1.0.0
 * @link http://www.lorinda.cc
 * @date 2016-12-09 
 *
 */
class BlogEncrypt_Plugin implements Typecho_Plugin_Interface
{
    public static function activate()
    {
        Typecho_Plugin::factory('index.php')->begin = array('BlogEncrypt_Plugin', 'check');
    }
    
    public static function deactivate(){}
	
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $pw = new Typecho_Widget_Helper_Form_Element_Textarea('pass', 
        NULL, '', 
        _t('访问密码'),
        _t('网站访问密码集合，每行一个'));
        $form->addInput($pw);
    }
    
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    
    public static function check()
    {
        //cookie设置，选中"记住我"cookie保存7天
        $cookie = array(
            'name'   => '__typecho_BlogEncrypt',
            'expire' => 7*24*3600
        );

        $pass = explode("\r\n", trim(Helper::options()->plugin('BlogEncrypt')->pass));

        $request = Typecho_Request::getInstance();
        
        if ($request->isPost() && $request->__isSet('BlogEncrypt')) {
            if ($request->BlogEncrypt && in_array($request->BlogEncrypt, $pass)) {
                Typecho_Cookie::set($cookie['name'], 
                    $request->BlogEncrypt, 
                    1 == $request->remember ? Helper::options()->gmtTime + Helper::options()->timezone + $cookie['expire'] : 0, 
                    Helper::options()->siteUrl);
            }
            //返回来源页 登录成功和失败均执行
            Typecho_Response::getInstance()->goBack();
        }
        
        // 保证action路由可用
        if (stripos(Typecho_Router::getPathInfo(), "action/") != 1) {
            $get = Typecho_Cookie::get($cookie['name']);
            if ($get && in_array($get, $pass)) return;
            //载入登录页面
            @require __TYPECHO_ROOT_DIR__ . '/' . __TYPECHO_PLUGIN_DIR__ . '/BlogEncrypt/' . "login.php";
            exit;
        }
    }
}
