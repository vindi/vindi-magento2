<?php


namespace Vindi\Payment\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {

        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'vindiconfiguration/general/webhook_key',
            'value' => self::generateRandomHash(),
        ];
  $setup->getConnection()
      ->insertOnDuplicate($setup->getTable('core_config_data'), $data, ['value']);


    }

    public static function generateRandomHash() {
        $length = 15;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $encoding = '8bit';

        if (false === ($max = mb_strlen($characters, $encoding))) {
            throw new \BadMethodCallException('Invalid encoding passed');
        }
        $string = '';
        $max--;
        for ($i = 0; $i < $length; ++$i) {
            $string .= $characters[mt_rand(0, $max)];
        }
        return $string;
    }
}
