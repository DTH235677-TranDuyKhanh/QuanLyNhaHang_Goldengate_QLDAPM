<?php
class DB {
    private static $pdo = null;

    public static function pdo() {
        if (!self::$pdo) {
            $c = cfg('db');
            $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset=utf8mb4";
            self::$pdo = new PDO($dsn, $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            self::$pdo->exec("SET time_zone = '+07:00'");
        }
        return self::$pdo;
    }
    public static function all($sql, $p = []) { $s = self::pdo()->prepare($sql); $s->execute($p); return $s->fetchAll(); }
    public static function one($sql, $p = []) { $s = self::pdo()->prepare($sql); $s->execute($p); $r = $s->fetch(); return $r ?: null; }
    public static function val($sql, $p = []) { $s = self::pdo()->prepare($sql); $s->execute($p); return $s->fetchColumn(); }
    public static function exec($sql, $p = []) { $s = self::pdo()->prepare($sql); $s->execute($p); return $s->rowCount(); }
    public static function insert($sql, $p = []) { self::exec($sql, $p); return (int)self::pdo()->lastInsertId(); }
}
