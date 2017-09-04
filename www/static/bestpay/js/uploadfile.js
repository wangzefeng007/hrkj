function imageUp(id){
	var name = $("#"+id).attr('name');
	//上传图片,保存原图，缩略图。
	$("#"+id).uploadify({        
        "swf"             : file_url+"/js/uploadify/uploadify.swf",
        "fileObjName"     : "download",
        "buttonText"      : "上传图片",
        "uploader"        : up_url,
        "width"           : '100%',
        "height"          : '100%',
        'removeTimeout'	  : 1,
        'fileTypeExts'	  : '*.jpg; *.png; *.gif;',
        "onUploadSuccess" : uploadSuccess
    });

	var bt = $("#"+id).parent().next('.image_group'), str = "";
	function uploadSuccess(file, data){
		str = '<li><input name="'+name+'" type="hidden" value="'+dir_url+data+'">';
		str += '<a class="fancybox" rel="group" href="'+dir_url+data+'" target="_blank"><img src="'+dir_url+'s_'+data+'" height="80" border="0"/></a>';
		str += '<a href="javascript:" class="close" onclick="close_img(this)">x</a></li>';
		if(data)
		{ 
			if (name.indexOf('[]')>0) {
				bt.append(str);
			}
			else {
				bt.html(str);
			}
		}
		else
		{
			alert('文件上传失败,请检查文件大小或类型');
		}
	}


    /*//图片拖拽排序
    bt.dragsort({
        placeHolderTemplate: "<li class='placeHolder'><div></div></li>",
        scrollSpeed: 0,
    });*/
}

function videoUp(id){
	var name = $("#"+id).attr('name');
	//上传图片,保存原图，缩略图。
	$("#"+id).uploadify({        
        "swf"             : file_url+"/js/uploadify/uploadify.swf",
        "fileObjName"     : "download",
        "buttonText"      : "上传视频",
        "uploader"        : up_url,
        "width"           : '100%',
        "height"          : '100%',
        'removeTimeout'	  : 1,
        'fileTypeExts'	  : '*.mp4',
        "onUploadSuccess" : uploadSuccess
    });

	var bt = $("#"+id).parent().prev('input'), str = "";
	function uploadSuccess(file, data){
		if (data)
		{
			bt.val(dir_url+data);
		}
		else
		{
			alert('文件上传失败,请检查文件大小或类型');
		}
	}


    /*//图片拖拽排序
    bt.dragsort({
        placeHolderTemplate: "<li class='placeHolder'><div></div></li>",
        scrollSpeed: 0,
    });*/
}

function close_img(obj){
	var ls = $(obj).parent('li');
	var img = ls.find('input').val();
	ls.remove();
	/* $.get( del_url,{file:img}, function(r){
		if(r.status){
			jSuccess(r.info,{
				TimeShown : 800,
				onClosed:function(){
					ls.remove();
				}
			});         
		}else{
			jError(r.info);
		}
	}); */
	return false;
}

/*function err(obj){
     obj.src = file_url+"/Images/error.jpg";    //替换图片地址
}*/