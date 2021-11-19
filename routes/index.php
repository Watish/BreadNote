<?php

use App\Controller\Auth;
use App\Controller\Hello;
use App\Controller\User;
use App\Controller\Notebook;
use App\Controller\Base;
use App\Controller\Note;
use App\Controller\Tag;
use App\Controller\Upload;
use App\Controller\Cache;

return function (Mix\Vega\Engine $vega) {
    $vega->handle('/hello', [new Hello(), 'index'])->methods('GET');
    //$vega->handle('/users/{id}', [new Users(), 'index'])->methods('GET');
    //$vega->handle('/auth', [new Auth(), 'index'])->methods('GET');
    
    $vega->handle('/api/auth/index', [new Auth(), 'index'])->methods('GET','POST');
    $vega->handle('/api/auth/login', [new Auth(), 'login'])->methods('GET','POST');
    $vega->handle('/api/auth/register', [new Auth(), 'register'])->methods('GET','POST');
    $vega->handle('/api/auth/logout', [new Auth(), 'logout'])->methods('GET','POST');

    $vega->handle('/api/user/info', [new User(), 'info'])->methods('GET','POST');
    $vega->handle('/api/user/updateUsername', [new User(), 'updateUsername'])->methods('GET','POST');
    $vega->handle('/api/user/updatePwd', [new User(), 'updatePwd'])->methods('GET','POST');
    $vega->handle('/api/user/updateLogo', [new User(), 'updateLogo'])->methods('GET','POST');
    $vega->handle('/api/user/getSyncState', [new User(), 'getSyncState'])->methods('GET','POST');
    
    $vega->handle('/api/notebook/getNotebooks', [new Notebook(), 'getNotebooks'])->methods('GET','POST');
    $vega->handle('/api/notebook/getSyncNotebooks', [new Notebook(), 'getSyncNotebooks'])->methods('GET','POST');
    $vega->handle('/api/notebook/addNotebook', [new Notebook(), 'addNotebook'])->methods('GET','POST');
    $vega->handle('/api/notebook/updateNotebook', [new Notebook(), 'updateNotebook'])->methods('GET','POST');
    $vega->handle('/api/notebook/deleteNotebook', [new Notebook(), 'deleteNotebook'])->methods('GET','POST');
    
    $vega->handle('/api/note/getNotes', [new Note(), 'getNotes'])->methods('GET','POST');
    $vega->handle('/api/note/getSyncNotes', [new Note(), 'getSyncNotes'])->methods('GET','POST');
    $vega->handle('/api/note/getNoteAndContent', [new Note(), 'getNoteAndContent'])->methods('GET','POST');
    $vega->handle('/api/note/getNoteContent', [new Note(), 'getNoteContent'])->methods('GET','POST');
    $vega->handle('/api/note/deleteTrash', [new Note(), 'deleteTrash'])->methods('GET','POST');
    $vega->handle('/api/note/addNote', [new Note(), 'addNote'])->methods('GET','POST');
    $vega->handle('/api/note/updateNote', [new Note(), 'updateNote'])->methods('GET','POST');
    
    $vega->handle('/api/tag/addTag', [new Tag(), 'addTag'])->methods('GET','POST');
    $vega->handle('/api/tag/getSyncTags', [new Tag(), 'getSyncTags'])->methods('GET','POST');
    $vega->handle('/api/tag/deleteTag', [new Tag(), 'deleteTag'])->methods('GET','POST');
    
    $vega->handle('/api/file/uploadAttach', [new Upload(), 'uploadAttach'])->methods('GET','POST');
    
};
