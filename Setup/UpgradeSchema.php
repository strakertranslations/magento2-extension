<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 27/10/16
 * Time: 10:37
 */

namespace Straker\EasyTranslationPlatform\Setup;


use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Straker\EasyTranslationPlatform\Model\AttributeTranslation;

/**
 * Upgrade the CatalogRule module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->addLabelColumn($setup);
        }

        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $this->increaseInt($setup, $context);
        }

//        if (version_compare($context->getVersion(), '1.0.5', '<')) {
//            $this->addIncrement($setup);
//        }

        $setup->endSetup();
    }

    /**
     * Remove Sub Product Discounts
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addLabelColumn(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $connection->addColumn(
            $setup->getTable(AttributeTranslation::ENTITY),
            'label',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Attribute Label'
            ]
        );
        $connection->addColumn(
            $setup->getTable(AttributeTranslation::ENTITY),
            'is_published',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Is Published'
            ]
        );
        $connection->addColumn(
            $setup->getTable(AttributeTranslation::ENTITY),
            'published_at',
            [
                'type' => Table::TYPE_TIMESTAMP,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Published Time'
            ]
        );
        $connection->addColumn(
            $setup->getTable(AttributeTranslation::ENTITY),
            'attribute_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Attribute Code'
            ]
        );
    }

    private function increaseInt(SchemaSetupInterface $setup){

        $connection = $setup->getConnection();
        $foreignKeys = $connection->getForeignKeys($setup->getTable('straker_attribute_option_translation'));

        foreach($foreignKeys as $foreignKey){
            $connection->dropForeignKey($setup->getTable('straker_attribute_option_translation'),$foreignKey['FK_NAME']);
        }

        $connection->modifyColumn(
            $setup->getTable('straker_attribute_option_translation'),
            'attribute_translation_id',
            [
                'type' => Table::TYPE_BIGINT,
            ]
        );

        $connection->modifyColumn(
            $setup->getTable('straker_attribute_translation'),
            'attribute_translation_id',
            [
                'type' => Table::TYPE_BIGINT,
            ]
        );

        $connection->addForeignKey(
            $setup->getFkName($setup->getTable('straker_attribute_option_translation'), 'attribute_translation_id', $setup->getTable('straker_attribute_translation'), 'attribute_translation_id'),
            $setup->getTable('straker_attribute_option_translation'),
            'attribute_translation_id',
            $setup->getTable('straker_attribute_translation'),
            'attribute_translation_id'
        );

        $connection->addForeignKey(
            $setup->getFkName($setup->getTable('straker_attribute_option_translation'), 'option_id', 'eav_attribute_option', 'option_id'),
            $setup->getTable('straker_attribute_option_translation'),
            'option_id',
            $setup->getTable('eav_attribute_option'),
            'option_id'
        );
    }

//    private function addIncrement(SchemaSetupInterface $setup)
//    {
//
//        $connection = $setup->getConnection();
//
//        $connection->modifyColumn(
//            $setup->getTable('straker_attribute_translation'),
//            'attribute_translation_id',
//            [
//                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
//                'comment' => 'Attribute Translation Id',
//                'primary' => true,
//                'auto_increment' => true,
//                'unsigned' => false,
//                'nullable' => false,
//            ]
//        );
//    }
}
