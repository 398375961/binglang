function install(){
	$.ajax({
		async: false, //同步请求
		url:'index.php?m=create_table&t=' + new Date().getTime(),
		dataType:'text',
		success: function(str){
			$('#result').append(str + '<br/>');
		}
	});
	$.get('index.php?m=check_install&t=' + new Date().getTime(),function(str){
		if(str == '1'){
			if(confirm('安装成功，是否跳转到首页？')){
				location.href = '../index.php';
			}
		}else{
			alert('安装失败！');
		}
	});
}