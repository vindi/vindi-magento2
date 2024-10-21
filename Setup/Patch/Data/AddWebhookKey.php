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
class AddWebhookKey implements DataPatchInterface
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

    protected function generateRandomHash(int $length = 15): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
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
