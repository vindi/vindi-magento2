<?php
namespace Vindi\Payment\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
/**
 * Class AddWebhookKey
 *
 * @package Vindi\Payment\Setup\Patch\Data
 */
class RemoveOldData implements DataPatchInterface
{
    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param WriterInterface $configWriter
     * @param ScopeConfigInterface $configResource
     */
    public function __construct(
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $configPath = 'vindiconfiguration/general/webhook_key';

        $existingConfig = $this->scopeConfig->getValue($configPath);

        if (!$existingConfig) {
            $webhookHey = self::generateRandomHash();
            $this->configWriter->save($configPath, $webhookHey);
        }
        return $this;
    }

    public static function generateRandomHash(): string
    {
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

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
