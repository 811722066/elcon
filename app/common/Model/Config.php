<?php
namespace Common\Model;

use Common\Cache;
use Common\Db;
use Common\Model;
use Phalcon\Db\Column;

class Config extends Model
{

    public static function all()
    {
        if (null === $config = Cache::get($key = 'db_configs')) {
            $db = Db::getInstance();
            $db->tableExists('config') or static::createConfigTable();

            $config = [];
            /* @var static $row */
            foreach (static::find() as $row) {
                $value = json_decode($row->getData('value'), true);
                $config[$row->getData('key')] = $value;
            }
            Cache::set($key, $config, Cache::TTL_ONE_MONTH);
        }
        return $config;
    }

    protected static function createConfigTable()
    {
        $db = Db::getInstance();
        $db->createTable('config', null, [
            'columns' => [
                new Column('key', [
                    'type' => Column::TYPE_VARCHAR,
                    'size' => 32,
                    'notNull' => true,
                    'primary' => true,
                ]),
                new Column('value', [
                    'type' => Column::TYPE_TEXT,
                ]),
            ],
        ]);
    }

    public static function saveConfig($key, $value)
    {
        is_array($value) and $value = json_encode($value);
        /* @var Config $config */
        $config = new static;
        $config->setData('key', $key)
            ->setData('value', $value)
            ->save();
    }
}
