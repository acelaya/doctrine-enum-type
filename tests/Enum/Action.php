<?php
namespace Acelaya\Test\Doctrine\Enum;

use MyCLabs\Enum\Enum;

/**
 * Class Action
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 *
 * @method static Action CREATE()
 * @method static Action READ()
 * @method static Action UPDATE()
 * @method static Action DELETE()
 */
class Action extends Enum
{
    const CREATE = 'create';
    const READ = 'read';
    const UPDATE = 'update';
    const DELETE = 'delete';
}
