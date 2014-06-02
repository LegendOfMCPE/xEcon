<?php

namespace xecon\utils;

class ExcelWriter{
	public static function export($dir, $name, array $data){
		$file = $dir.((substr($dir, -1) === "/" or substr($dir, -1) === "\\" )? "":"/")."$name.xml";
		$s = fopen($file, "wt");
		if("init" === "init"){
			fwrite($s, <<<EOI
<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
	xmlns:o="urn:schemas-microsoft-com:office:office"
	xmlns:x="urn:schemas-microsoft-com:office:excel"
	xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
	xmlns:html="http://www.w3.org/TR/REC-html40">
	<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
		<Version>12.00</Version>
	</DocumentProperties>
	<ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
		<WindowHeight>10005</WindowHeight>
		<WindowWidth>10005</WindowWidth>
		<WindowTopX>120</WindowTopX>
		<WindowTopY>135</WindowTopY>
		<ProtectStructure>False</ProtectStructure>
		<ProtectWindows>False</ProtectWindows>
	</ExcelWorkbook>
	<Styles>
		<Style ss:ID="Default" ss:Name="Normal">
			<Alignment ss:Vertical="Center"/>
			<Borders/>
			<Font ss:FontName="Comic Sans MS" x:CharSet="136" x:Family="Roman" ss:Size="12"
				ss:Color="#000000"/>
			<Interior/>
			<NumberFormat/>
			<Protection/>
		</Style>
		<Style ss:ID="s63">
			<NumberFormat ss:Format="&quot;X$&quot;#,##0.00"/>
		</Style>
		<Style ss:ID="s64">
			<NumberFormat ss:Format="Short Date"/>
		</Style>
	</Styles>

EOI
			);
		}
		foreach($data as $worksheet=>$rows){
			// write each worksheet
			// calculate max rowssize
			$max = 0;
			foreach($rows as $cols)
				$max = max($max, count($cols));
			// out write
			fwrite($s, "\t<Worksheet ss:Name=\"$worksheet\">
		<Table ss:ExpandedColumnCount=\"$max\" ss:ExpandedRowCount=\"".count($rows)."\" x:FullColumns=\"1\" x:FullRows=\"1\" ss:DefaultColumnWidth=\"54\" ss:DefaultRowHeight=\"16/5\">
");
			// write each row
			foreach($rows as $row=>$cells){
				fwrite($s, "\t\t\t<Row ss:AutoFitHeight=\"0\">
");
				foreach($cells as $cell){
					if(is_string($cell) and substr($cell, 0, 1) === "\$" and is_numeric(substr($cell, 1)))
						fwrite($s, "\t\t\t\t<Cell ss:StyleID=\"s63\"><Data ss:Type=\"Number\">".substr($cell, 1)."</Data></Cell>
");
					elseif(is_string($cell) and substr($cell, 0, 8) === "__DATE__" and is_numeric(str_replace("T", "", substr($cell, 8))))
						fwrite($s, "\t\t\t\t<Cell ss:StyleID=\"s64\"><Data ss:Type=\"DateTime\">".substr($cell, 8)."</Data></Cell>
");
					elseif(is_string($cell) and !is_numeric($cell))
						fwrite($s, "\t\t\t\t<Cell><Data ss:Type=\"String\">$cell</Data></Cell>
");
					elseif(is_numeric($cell))
						fwrite($s, "\t\t\t\t<Cell><Data ss:Type=\"Number\">$cell</Data></Cell>
");
				}
				fwrite($s, "\t\t\t</Row>
");
			}
			fwrite($s, "\t\t</Table>
	</Worksheet>
");
		}
			fwrite($s, "</Workbook>
");
		fclose($s);
	}
	// public static function arrToStr($var){
		// $output = "array(";
		// foreach($var as $key=>$value){
			// $output .= is_string($key) ? "\"$key\"":$key;
			// $output .= "=>";
			// if(is_string($value))
				// $output .= "\"$value\"";
			// elseif(is_array($value))
				// $output .= self::arrToStr($var);
			// else $output .= "$value";
			// $output .= ", ";
		// }
		// $output = substr($output, 0, -2).")";
		// return $output;
	// }
}
