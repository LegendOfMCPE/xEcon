<?php

namespace xecon\utils;

use pocketmine\Player;
use xecon\XEcon;

class ExcelWriter{
	const TYPE = 0;
	const TARGET_NAME = 1;
	const TARGET_ACCOUNT = 2;
	const AMOUNT = 3;
	const DETAILS = 4;
	const DATE = 5; // TODO replace these with config properties
	public static function export($dir, $name, array $data){
		$file = $dir . ((substr($dir, -1) === "/" or substr($dir, -1) === "\\" )? "":"/")."$name.xml";
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
		<Table ss:ExpandedColumnCount=\"$max\" ss:ExpandedRowCount=\"" . count($rows) . "\" " .
				"x:FullColumns=\"1\" x:FullRows=\"1\" ss:DefaultColumnWidth=\"54\" " .
				"ss:DefaultRowHeight=\"16/5\">
");
			// write each row
			foreach($rows as $cells){
				fwrite($s, "\t\t\t<Row ss:AutoFitHeight=\"0\">
");
				foreach($cells as $cell){
					if(is_string($cell) and substr($cell, 0, 1) === "\$" and is_numeric(substr($cell, 1)))
						fwrite($s, "\t\t\t\t<Cell ss:StyleID=\"s63\"><Data ss:Type=\"Number\">" . substr($cell, 1)."</Data></Cell>
");
					elseif(is_string($cell) and substr($cell, 0, 8) === "__DATE__" and is_numeric(str_replace("T", "", substr($cell, 8))))
						fwrite($s, "\t\t\t\t<Cell ss:StyleID=\"s64\"><Data ss:Type=\"DateTime\">" . substr($cell, 8)."</Data></Cell>
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
	/**
	 * @param XEcon $main
	 * @param Player $player
	 * @param int $from
	 * @param int $to
	 * @param int|string $fromToFilter T_LOGICAL_OR for all transactions, T_LOGICAL_AND for self-to-self inter-account transactions, T_LOGICAL_XOR for inter-entity transactions
	 * @return string
	 */
	public static function exportTransactions(XEcon $main, Player $player, $from = 0, $to = PHP_INT_MAX, $fromToFilter = T_LOGICAL_OR){
		$ent = $main->getSession($player)->getEntity();
		$data = $main->getTransactions($ent->getAbsolutePrefix(), $ent->getName(), null, $ent->getAbsolutePrefix(), $ent->getName(), null, $from, $to, 0, PHP_INT_MAX, $fromToFilter);
		$dir = $main->getDataFolder() . "logs/";
		$file = strtolower($player->getName()) . " transaction logs on " . date("F j, Y") .
			" at " . date("H.i.s (e O, T)");
		$serialized = serialize($data);
		$lowName = strtolower($player->getName());
		$onRun = function() use($dir, $file, $serialized, $lowName){
			@mkdir($dir);
			/** @var \xecon\log\Transaction[] $rawData */
			$rawData = unserialize($serialized);
			$output = [];
			foreach($rawData as $transaction){
				$timestamp = $transaction->getTimestamp();
				$out = [
					self::TYPE => "Pay", // pay or receive
					self::TARGET_NAME => "{$transaction->getToType()} {$transaction->getToName()}", // name of the entity paid to/received from
					self::TARGET_ACCOUNT => $transaction->getToAccount(), // account paid to/received from
					self::AMOUNT => $transaction->getAmount(), // amount paid
					self::DETAILS => $transaction->getDetails(), // details, if exists, or "none"
					self::DATE => "__DATE__$timestamp", // "__DATE__" followed by the timestamp
				];
				if($transaction["totype"] === "Player" and $transaction["toname"] === $lowName){
					$out[self::TYPE] = "Receive";
					$out[self::TARGET_NAME] = "{$transaction->getFromType()} {$transaction->getFromName()}";
					$out[self::TARGET_ACCOUNT] = $transaction->getFromAccount();
				}
				$output[] = $out;
			}
			self::export($dir, $file, $output);
		};
		$onCompletion = function() use($main, $dir, $file){
			$main->getLogger()->info("Exported transaction logs to $dir$file");
		};
		$task = new CallbackAsyncTask($onRun, $onCompletion);
		$main->getServer()->getScheduler()->scheduleAsyncTask($task);
		return $dir . $file;
	}
}
