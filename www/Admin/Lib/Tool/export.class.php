<?php
/*
*	文件导出类
*/
class export
{
	/*
	*	导出xls文件
	*/
	static public function xls($title,$data,$filename,$fields = array())
	{
		Vendor('Excel.ExcelCommon');
		Vendor('Excel.PHPExcel');
		Vendor('Excel.PHPExcel.Writer.Excel5.php');
        //导出xls 开始
		$relatPath = '/Upload/temp.xls';
		$absoPath = dirname(dirname(dirname(dirname(__FILE__))));
		$tempFile = $absoPath . $relatPath;
		if(file_exists($tempFile)){
			unlink($tempFile);
		}

		ExcelCommon::export($filename, $tempFile, $title, $data);
		ExcelCommon::download($tempFile, $filename, 'gb2312');
	}
	
	/*
	*	导出txt文件
	*/
	static public function txt($data,$filename)
	{
		header("Content-Type: application/octet-stream");   
		header('Content-Disposition: attachment; filename="' . $filename . '.txt"');
		echo $data;
		exit;
	}
	
}
