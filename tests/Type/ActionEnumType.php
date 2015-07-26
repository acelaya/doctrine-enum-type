<?php
namespace Acelaya\Test\Doctrine\Type;

use Acelaya\Doctrine\Type\AbstractPhpEnumType;

/**
 * Class ActionEnumType
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class ActionEnumType extends AbstractPhpEnumType
{
    protected $enumType = 'Acelaya\Test\Doctrine\Enum\Action';

    /**
     * @return string
     */
    protected function getSpecificName()
    {
        return 'action';
    }
}
