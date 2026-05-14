<?php

namespace Abbott\MyAccount\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\BlockFactory;

class AbbottStaticBlock implements DataPatchInterface, PatchVersionInterface
{
    private $moduleDataSetup;

    private $blockFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockFactory $blockFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
    }

    public function apply()
    {
        $similac_policies = "<p>I'd like to receive news and information from SimilacStore and Abbott Laboratories. I understand that the information I've provided will be used by Abbott and its contracted third parties to provide me with helpful information about SimilacStore and to send me marketing materials and promotional offers by email or regular mail about Abbott's products and services. Abbott will not sell or transfer my personal information to any other party for their marketing activities. To be removed from SimilacStore's mailing list, contact 1-800-258-7677.</p>
                    <p>I understand that the information I've provided will be used only by Abbott and its contracted third parties to contact me by mail and email with helpful information and offers about Similac, Similac StrongMoms Rewards, and other Abbott products and services, and, if I provide my telephone number, it may be used (only by Abbott) to provide helpful advice. Abbott will not sell or transfer my name or contact information to any third party for their marketing use. To be removed from our mailing list or request a copy of this information, contact 800-232-7677. For additional details about how we keep your information confidential, please see our <a href=\"https://abbottnutrition.com/privacy-policy\">Privacy Policy</a>. Limit one enrollment per household. Offers and values may vary. <a href=\"https://similac.com/strongmoms/terms-conditions\">Terms and conditions </a></p>";

        $similac_note = "<p>* Providing your personal information through this form will be treated the same as if you provided a physical signature.<br/>
                         ** Offers may vary.</p>";

        $similac_cart_terms_conditions = "<p><strong>Terms and Conditions :</strong></p>"
                . "<p>I agree to the <a href=\"http://www.abbott.com/online-terms-and-conditions.html\" target=\"_blank\">terms &amp; conditions.</a> By clicking \"subscribe checkout,\" you accept our terms and your subscription will renew monthly and you will be charged the monthly subscription fees each month after the date you sign up. You can cancel anytime at least 5 business days before your next shipment to avoid being charged. The monthly charge may change your product selection or we change our prices or shipping (with notice to you).</p>";

        $similac_contact_preference = "I'd like to receive news and information from SimilacStore and Abbott Laboratories. I understand that the information I've provided will be used by Abbott and its contracted third parties to provide me with helpful information about SimilacStore and to send me marketing materials and promotional offers by email or regular mail about Abbott's products and services. Abbott will not sell or transfer my personal information to any other party for their marketing activities. To be removed from SimilacStore's mailing list, contact 1-800-258-7677.";

        $abbott_contact_preference = "I'd like to receive news and information from AbbottStore and Abbott Laboratories. I understand that the information I've provided will be used by Abbott and its contracted third parties to provide me with helpful information about AbbottStore and to send me marketing materials and promotional offers by email or regular mail about Abbott's products and services. Abbott will not sell or transfer my personal information to any other party for their marketing activities. To be removed from AbbottStore's mailing list, contact 1-800-258-7677.";

        $glucerna_contact_preference = "I'd like to receive news and information from GlucernaStore, Glucerna and Abbott Laboratories. I understand that the information I've provided will be used by Abbott and its contracted third parties to provide me with helpful information about GlucernaStore and to send me marketing materials and promotional offers by email or regular mail about Abbott's products and services. Abbott will not sell or transfer my personal information to any other party for their marketing activities. To be removed from GlucernaStore's mailing list, contact 1-877-457-0524.";

        $abbottStaticBlocks = [
            [
                'title' => 'Similac Policies',
                'identifier' => 'similac_policies',
                'content' => $similac_policies,
                'is_active' => 1,
                'stores' => 3,
            ],
            [
                'title' => 'Similac Subscription Note',
                'identifier' => 'similac_subscription_note',
                'content' => $similac_note,
                'is_active' => 1,
                'stores' => 3,
            ],
            [
                'title' => 'Similac Terms and Conditions',
                'identifier' => 'similac_cart_terms_and_conditions',
                'content' => $similac_cart_terms_conditions,
                'is_active' => 1,
                'stores' => 3,
            ],
            [
                'title' => 'Similac Contact Preference',
                'identifier' => 'similac_contact_preference',
                'content' => $similac_contact_preference,
                'is_active' => 1,
                'stores' => 3,
            ],
            [
                'title' => 'Abbott Contact Preference',
                'identifier' => 'abbott_contact_preference',
                'content' => $abbott_contact_preference,
                'is_active' => 1,
                'stores' => 1,
            ],
            [
                'title' => 'Glucerna Contact Preference',
                'identifier' => 'glucerna_contact_preference',
                'content' => $glucerna_contact_preference,
                'is_active' => 1,
                'stores' => 2,
            ]
            ];
        $this->moduleDataSetup->startSetup();
        foreach ($abbottStaticBlocks as $abbottBlocks) {
            $this->blockFactory->create()->setData($abbottBlocks)->save();
        }

        $this->moduleDataSetup->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public static function getVersion()
    {
        return '2.0.0';
    }

    public function getAliases()
    {
        return [];
    }
}
