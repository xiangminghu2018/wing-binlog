<?php namespace Wing\Bin;
use Wing\Library\PDO;

/**
 * Db.php
 * User: huangxiaoan
 * Created: 2017/9/13 17:13
 * Email: huangxiaoan@xunlei.com
 */
class Db
{
	/**
	 * @var PDO
	 */
	public static $pdo;
	public static function getChecksum()
	{
		$res = self::$pdo->row("SHOW GLOBAL VARIABLES LIKE 'BINLOG_CHECKSUM'");
		return $res['Value'];
	}

	public static function getPos() {
		$sql    = "SHOW MASTER STATUS";
		$result = self::$pdo->row($sql);
		return $result;
	}

	public static function getFields($schema, $table) {

		$sql = "SELECT
                COLUMN_NAME,COLLATION_NAME,CHARACTER_SET_NAME,COLUMN_COMMENT,COLUMN_TYPE,COLUMN_KEY
                FROM
                information_schema.columns
                WHERE
                table_schema = '{$schema}' AND table_name = '{$table}'";
		$result = self::$pdo->query($sql);
		return $result;
	}
}