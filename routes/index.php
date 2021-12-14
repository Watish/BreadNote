<?php

use App\Controller\Auth;
use App\Controller\Index;
use App\Controller\User;
use App\Controller\Notebook;
use App\Controller\Base;
use App\Controller\Note;
use App\Controller\Tag;
use App\Controller\Upload;
use App\Controller\Cache;

return function (Mix\Vega\Engine $vega) {
    
    /**
     * 前端路由
     *  /
     */
    $vega->handle('/',[new Index(), 'index'])->methods('GET');
    $vega->handle('/userLogin',[new Index(), 'index'])->methods('GET');
    $vega->handle('/BreadNote',[new Index(), 'index'])->methods('GET');

    
    /**
     * 测试路由
     *  /hello
     */
    $vega->handle('/hello',[new Index(), 'hello'])->methods('GET');
    
    
    /**
     * 接口路由
     *   前缀/api
     */
    $api = $vega->pathPrefix('/api');
        $api->handle('/auth/index', [new Auth(), 'index'])->methods('GET','POST');
        $api->handle('/auth/login', [new Auth(), 'login'])->methods('GET','POST');
        $api->handle('/auth/register', [new Auth(), 'register'])->methods('GET','POST');
        $api->handle('/auth/logout', [new Auth(), 'logout'])->methods('GET','POST');
        $api->handle('/user/info', [new User(), 'info'])->methods('GET','POST');
        $api->handle('/user/updateUsername', [new User(), 'updateUsername'])->methods('GET','POST');
        $api->handle('/user/updatePwd', [new User(), 'updatePwd'])->methods('GET','POST');
        $api->handle('/user/updateLogo', [new User(), 'updateLogo'])->methods('GET','POST');
        $api->handle('/user/getSyncState', [new User(), 'getSyncState'])->methods('GET','POST');
        $api->handle('/notebook/getNotebooks', [new Notebook(), 'getNotebooks'])->methods('GET','POST');
        $api->handle('/notebook/getSyncNotebooks', [new Notebook(), 'getSyncNotebooks'])->methods('GET','POST');
        $api->handle('/notebook/addNotebook', [new Notebook(), 'addNotebook'])->methods('GET','POST');
        $api->handle('/notebook/updateNotebook', [new Notebook(), 'updateNotebook'])->methods('GET','POST');
        $api->handle('/notebook/deleteNotebook', [new Notebook(), 'deleteNotebook'])->methods('GET','POST');
        $api->handle('/note/getNotes', [new Note(), 'getNotes'])->methods('GET','POST');
        $api->handle('/note/getSyncNotes', [new Note(), 'getSyncNotes'])->methods('GET','POST');
        $api->handle('/note/getNoteAndContent', [new Note(), 'getNoteAndContent'])->methods('GET','POST');
        $api->handle('/note/getNoteContent', [new Note(), 'getNoteContent'])->methods('GET','POST');
        $api->handle('/note/deleteTrash', [new Note(), 'deleteTrash'])->methods('GET','POST');
        $api->handle('/note/addNote', [new Note(), 'addNote'])->methods('GET','POST');
        $api->handle('/note/updateNote', [new Note(), 'updateNote'])->methods('GET','POST');
        $api->handle('/tag/addTag', [new Tag(), 'addTag'])->methods('GET','POST');
        $api->handle('/tag/getSyncTags', [new Tag(), 'getSyncTags'])->methods('GET','POST');
        $api->handle('/tag/deleteTag', [new Tag(), 'deleteTag'])->methods('GET','POST');
        $api->handle('/file/uploadAttach', [new Upload(), 'uploadAttach'])->methods('GET','POST');
    
    /**
     * 前端资源路由
     *  /
     */
    $vega->static('/', __DIR__. '/../storage/');
};
