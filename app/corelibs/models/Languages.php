<?php
/**
 * Created by IntelliJ IDEA.
 * User: DenysBarsuk
 * Date: 11.06.2018
 * Time: 11:11
 */

namespace Schedule\Core\Models;


use Phalcon\Mvc\Model;

class CommentsToRoutes extends Model
{

    public function getSource()
    {
        return 'comments_to_routes';
    }
}