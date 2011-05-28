<?php
include ("connection.php");

// Parameters
$req_device_id = $_GET["device_id"];
$req_document_srl = $_GET["document_srl"];
$req_tag_srl_list = $_GET["tag_srl_list"];

// Fetch Thread List
$query = "SELECT DISTINCT documents.* FROM document_tags LEFT OUTER JOIN documents ON document_tags.document_srl = documents.document_srl WHERE 1=1 ";

// 태그 리스트에 포함된 글들 모두 포함
if ($req_tag_srl_list) {
	$query.= " AND document_tags.tag_srl IN (".$req_tag_srl_list.")";
}

// 자기가 남긴 글 보기
if ($req_device_id) {
	$query.= " AND device_id = '".$req_device_id."'";
}

// 글 하나만 보기
if ($req_document_srl) {
	$query.= " AND documents.document_srl = $req_document_srl";
}
$query.= " ORDER BY timestamp DESC";

//echo $query;
$result = mysql_query($query, $connect) or die(" : ".mysql_error());

// XML Output
header("Content-Type: text/xml;");
$writer = new XMLWriter();
$writer->openURI('php://output');
$writer->startDocument('1.0','UTF-8');
$writer->setIndent(4);

$writer->startElement('THREADLIST');
while($row=mysql_fetch_array($result))
{
	$query_count = "SELECT count(*) AS comment_count FROM comments WHERE document_srl = ".$row[document_srl];
	$result_count = mysql_query($query_count, $connect) or die("counting error : ".mysql_error());
	$row_count = mysql_fetch_array($result_count);
	
	$writer->startElement('THREAD');
	$writer->writeAttribute('document_srl', $row[document_srl]);
	$writer->writeAttribute('nick_name', $row[nick_name]);
	$writer->writeAttribute('picture_path', $row[picture_path]);
	$writer->writeAttribute('audio_path', $row[audio_path]);
	$writer->writeAttribute('latitude', $row[latitude]);
	$writer->writeAttribute('longitude', $row[longitude]);
	$writer->writeAttribute('timestamp', strtotime("now")-strtotime($row[timestamp]));
	$writer->writeAttribute('device_id', $row[device_id]);
	$writer->writeAttribute('content', $row[content]);
	$writer->writeAttribute('comment_count', $row_count[comment_count]);
	$writer->endElement();
}
$writer->endElement();
$writer->endDocument();
$writer->flush();
?>