function CKphone(){
	var phone = document.getElementById("inputPhone");
	if(phone.value==""){
		alert("手机号码不能为空！！");
		phone.focus();
		return false;
	}
	if(phone.value!=""){
		if(isUserPhone(phone.value)==false){
			alert("您输入的手机号码有误！！");
			phone.focus();
			return false;
		}
	}
	return true;
}
function CkUsers(theForm){	
	var password = document.getElementById("inputPassword");
	if(password.value==""){
		alert("密码不能为空！！");
		password.focus();
		return false;
	}
	if(password.value!=""){
		if(isPasswords(password.value)==false){
			alert("您输入密码格式有误！！");
			password.focus();
			return false;
		}
	}
	if(theForm.password.value!=theForm.repassword.value){
		alert("两次输入的密码有误！！");
		theForm.repassword.focus();
		return false;
	}
	if(theForm.verify.value==""){
		alert("验证码不能为空！！");
		theForm.verify.focus();
		return false;
	}
	return true;
}
//检查报名元素
function CkApply(theForm){
	var mobile = document.getElementById("mobile");
	
	if(theForm.brand.value=="0"){
		alert("请选择团购品牌！");
		theForm.brand.focus();
		return false;
	}
	if(theForm.city.value=="0"){
		alert("请选择团购市！");
		theForm.city.focus();
		return false;
	}
	if(theForm.area.value=="0"){
		alert("请选择团购区！");
		theForm.area.focus();
		return false;
	}
	if(theForm.username.value==""){
		alert("请输入姓名！");
		theForm.username.focus();
		return false;
	}
	if(mobile.value==""){
		alert("请输入手机号码！");
		mobile.focus();
		return false;
	}
	if(mobile.value!=""){
		if(isUserPhone(mobile.value)==false){
			alert("您输入手机号码有误！！");
			mobile.focus();
			return false;
		}
	}
	return true;
}
//清空表单元素
function clearForm(form) {
  $(':input', form).each(function() {
    var type = this.type;
    var tag = this.tagName.toLowerCase(); 
    if (type == 'text' || type == 'password' || tag == 'textarea')
      this.value = "";
    else if (type == 'checkbox')
      this.checked = false;
    else if (tag == 'select')
      this.selectedIndex = 0;
	  var next=$(this).find("option[value='0']").text();
	  $(this).parent().find('label').html(next);
  });
};
function CkInfo(theForm){	
	if(theForm.nickname.value==""){
		alert("昵称不能为空！！");
		theForm.nickname.focus();
		return false;
	}
	if(theForm.username.value==""){
		alert("姓名不能为空！！");
		theForm.username.focus();
		return false;
	}
	if(theForm.qq.value==""){
		alert("QQ不能为空！！");
		theForm.qq.focus();
		return false;
	}
	return true;
}
function CkPrints(theForm){	
	if(theForm.say.value==""){
		alert("请填写发布内容！");
		theForm.say.focus();
		return false;
	}
	if(theForm.pic.value==""){
		alert("请添加发布图片！");
		theForm.pic.focus();
		return false;
	}
	return true;
}
function CkUser(theForm){
	if(theForm.user_name.value==""){
		alert("账户名不能为空！！");
		theForm.user_name.focus();
		return false;
	}
	if(theForm.verify.value==""){
		alert("验证码不能为空！！");
		theForm.verify.focus();
		return false;
	}
	return true;
}
function CkVerify(theForm){
	if(theForm.verifys.value==""){
		alert("验证码不能为空！！");
		theForm.verifys.focus();
		return false;
	}
	return true;
}
function CkPassword(theForm){
	if(theForm.user_pwd.value==""){
		alert("新密码不能为空！！");
		theForm.user_pwd.focus();
		return false;
	}
	if(theForm.pwd.value==""){
		alert("确认密码不能为空！！");
		theForm.pwd.focus();
		return false;
	}
	if(theForm.user_pwd.value!=theForm.pwd.value){
		alert("两次密码输入不一致！");
		theForm.pwd.focus();
		return fales;
	}
	return true;
}
/*正则表达式邮箱验证*/
function isEmail(yx){
 var reyx= /^([a-zA-Z0-9_\.])+@([a-zA-Z0-9_])+(.[a-zA-Z0-9_])+/;
 return(reyx.test(yx));
}
/*校验用户密码*/
function isPasswords(yx){
 var reyx= /^[0-9 | A-Z | a-z]{6,16}$/;
 return(reyx.test(yx));
}
/*校验用户电话号码*/
function isUserPhone(s) {
	var patrn = /^(1[3|5|8])[\d]{9}/;
	if (!patrn.exec(s))
		return false;
	return true;
}
/*校验产品数量*/
function isNumber(s) {
	var patrn = /^[0-9]*[1-9][0-9]*$/;
	if(!patrn.exec(s))
		return false;
	return true;
}

/*检查退换货元素*/
function checkUserIsNull(theForm) {
	if(theForm.length.value==""){
		alert("您提交的数量不能为空！");
		theForm.length.focus();
		return false;
	}
	if(theForm.length.value!=""){
		if(isNumber(theForm.length.value)==false){
			alert("您提交的产品数量有误！！");
			theForm.length.focus();
			return false;
		}
	}
	if(theForm.question.value==""){
		alert("您输入的商品问题不能为空！");
		theForm.question.focus();
		return false;
	}
	if(theForm.address.value==""){
		alert("您输入的收货地址不能为空！");
		theForm.address.focus();
		return false;
	}
	if(theForm.name.value==""){
		alert("您输入的联系人不能为空！");
		theForm.name.focus();
		return false;
	}
	if(theForm.tel.value==""){
		alert("您输入的手机号码不能为空！");
		theForm.tel.focus();
		return false;
	}
	if(theForm.tel.value!=""){
		if(isUserPhone(theForm.tel.value)==false){
			alert("您输入的电话号码有误！！");
			theForm.tel.focus();
			return false;
		}
	}
	
}
/*检查地址元素*/
function checkAddr(theForm) {
	if(theForm.addressee.value==""){
		alert("您输入的联系人不能为空！");
		theForm.addressee.focus();
		return false;
	}
	if(theForm.tel.value==""){
		alert("您输入的手机号码不能为空！");
		theForm.tel.focus();
		return false;
	}
	if(theForm.tel.value!=""){
		if(isUserPhone(theForm.tel.value)==false){
			alert("您输入的电话号码有误！！");
			theForm.tel.focus();
			return false;
		}
	}
	if(theForm.address.value==""){
		alert("您输入的收货地址不能为空！");
		theForm.address.focus();
		return false;
	}
}

/*检查投诉元素*/
function checkContentIsNull(theForm) {
	if(theForm.content.value==""){
		alert("您提交的投诉内容不能为空！");
		theForm.content.focus();
		return false;
	}
}
/*检查用户名*/
function checkNicknameIsNull(theForm) {
	if(theForm.nickname.value==""){
		alert("您提交的昵称不能为空！");
		theForm.nickname.focus();
		return false;
	}
}
/*多位数表示*/
function fix(num, length) {
  return ('' + num).length < length ? ((new Array(length + 1)).join('0') + num).slice(-length) : '' + num;
}

/*提交表单*/

function submitForm(){
 	document.getElementById("myform").submit();
}

/* 上传图片预览弹出层 */
function tanchu(obj){
	var winW = $(window).width();
    var winH = $(window).height();
	var src = obj.src;
	if(src === undefined){
		return false;
	}
	// 创建弹出框以及获取弹出图片
    var imgPopup = "<div id=\"uploadPop\" class=\"upload-img-popup\"></div>"
	//如果弹出层存在，则不能再弹出
    var popupLen = $(".upload-img-popup").length;
    if( popupLen < 1 ) {
    	$(imgPopup).appendTo("body");
        $(".upload-img-popup").html(
        	"<img src='"+ src +"' >" + "<a class=\"close-pop\" href=\"javascript:;\" title=\"关闭\"></a>"
        );
    }
	// 弹出层定位
  /*  var uploadImg = $("#uploadPop").find("img");
    var popW = uploadImg.width();
    var popH = uploadImg.height();
    var left = (winW -popW)/2;
    var top = (winH - popH)/2 + 50;
	if(popW>winW){
		left = 50;
		uploadImg.css({
			"max-width":winW * 0.8,
		});
	}*/
	/*if(popH>winH){
		top = 80;
		uploadImg.css({
			"max-height":winH * 0.8,
		});
	}*/
/*    $(".upload-img-popup").css({
        "left": left,
        "top": top,
    });
}*/

//加入收藏
function AddFavorite(sURL, sTitle) {
	sURL = encodeURI(sURL); 
	try{   
 		window.external.addFavorite(sURL, sTitle);   
		}catch(e) {   
		try{   
			window.sidebar.addPanel(sTitle, sURL, "");   
			}catch (e) {   
			alert("加入收藏失败，请使用Ctrl+D进行添加,或手动在浏览器里进行设置.");
			}   
 		}
 }
 
 //设为首页
function SetHome(url){
	if (document.all) {
		document.body.style.behavior='url(#default#homepage)';
		document.body.setHomePage(url);
	}else{
 		alert("您好,您的浏览器不支持自动设置页面为首页功能,请您手动在浏览器里设置该页面为首页!");
 	}
 }
 
}