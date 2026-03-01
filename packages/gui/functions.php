<?php

use CentralBooking\GUI\ComponentInterface;
use CentralBooking\GUI\DisplayerInterface;

function git_string_to_component(string $string)
{
    return CentralBooking\GUI\ComponentBuilder::create($string);
}

function git_login_form()
{
    return new CentralBooking\GUI\LoginDisplay();
}

/**
 * @param array{name:string,type:string,value:string,required:bool,disabled:bool} $args
 * @return CentralBooking\GUI\InputComponent
 */
function git_input_field(array $args = [])
{
    $input = new CentralBooking\GUI\InputComponent(
        $args['name'] ?? '',
        $args['type'] ?? 'text'
    );
    if (isset($args['id'])) {
        $input->id = $args['id'];
    }
    if (isset($args['class'])) {
        $classes = explode(' ', $args['class']);
        foreach ($classes as $class) {
            $input->class_list->add($class);
        }
    }
    if (isset($args['value'])) {
        $input->setValue($args['value']);
    }
    if (isset($args['required'])) {
        $input->setRequired($args['required']);
    }
    if (isset($args['disabled'])) {
        $input->setDisabled($args['disabled']);
    }
    unset($args['name'], $args['type'], $args['id'], $args['class'], $args['value'], $args['required'], $args['disabled']);
    foreach ($args as $attr => $val) {
        $input->attributes->set($attr, $val);
    }
    return $input;
}

/**
 * @param array{name:string,title:string,options:array,value:string,required:bool,disabled:bool} $args
 * @return CentralBooking\GUI\SelectComponent
 */
function git_select_field(array $args = [])
{
    $select = new CentralBooking\GUI\SelectComponent(
        $args['name'] ?? '',
        $args['title'] ?? ''
    );
    if (isset($args['id'])) {
        $select->id = $args['id'];
    }
    if (isset($args['class'])) {
        $classes = explode(' ', $args['class']);
        foreach ($classes as $class) {
            $select->class_list->add($class);
        }
    }
    foreach ($args['options'] ?? [] as $value => $label) {
        $select->addOption($value, $label);
    }
    if (isset($args['value'])) {
        $select->setValue($args['value']);
    }
    if (isset($args['required'])) {
        $select->setRequired($args['required']);
    }
    if (isset($args['disabled'])) {
        $select->setDisabled($args['disabled']);
    }
    unset($args['name'], $args['title'], $args['options'], $args['id'], $args['class'], $args['value'], $args['required'], $args['disabled']);
    foreach ($args as $attr => $val) {
        $select->attributes->set($attr, $val);
    }
    return $select;
}

/**
 * @param array{name:string,options:array,values:array,required:bool,disabled:bool} $args
 * @return CentralBooking\GUI\MultipleSelectComponent
 */
function git_multiselect_field(array $args = [])
{
    $select = new CentralBooking\GUI\MultipleSelectComponent(
        $args['name'] ?? '',
    );
    foreach ($args['options'] ?? [] as $value => $label) {
        $select->addOption($value, $label);
    }
    if (isset($args['values'])) {
        foreach ($args['values'] as $value) {
            $select->setValue($value);
        }
    }
    if (isset($args['required'])) {
        $select->setRequired($args['required']);
    }
    if (isset($args['disabled'])) {
        $select->setDisabled($args['disabled']);
    }
    return $select;
}

/**
 * @param array{name:string,text:string,required:bool,disabled:bool} $args
 * @return CentralBooking\GUI\TextareaComponent
 */
function git_textarea_field(array $args = [])
{
    $textarea = new CentralBooking\GUI\TextareaComponent($args['name'] ?? '', );
    if (isset($args['text'])) {
        $textarea->setValue($args['text']);
    }
    if (isset($args['required'])) {
        $textarea->setRequired($args['required']);
    }
    if (isset($args['disabled'])) {
        $textarea->setDisabled($args['disabled']);
    }
    return $textarea;
}

function git_code_editor_area_field(array $args = [])
{
    $code_editor = new CentralBooking\GUI\CodeEditorComponent($args['name'] ?? '');
    if (isset($args['value'])) {
        $code_editor->setValue(stripslashes($args['value']));
    }
    if (isset($args['required'])) {
        $code_editor->setRequired($args['required']);
    }
    if (isset($args['disabled'])) {
        $code_editor->setDisabled($args['disabled']);
    }
    if (isset($args['language'])) {
        $code_editor->set_language($args['language']);
    }
    unset($args['name'], $args['value'], $args['required'], $args['disabled'], $args['language']);
    foreach ($args as $attr => $val) {
        $code_editor->attributes->set($attr, $val);
    }
    return $code_editor;
}

/**
 * @param array{label:string,content:ComponentInterface|DisplayerInterface}[] $args
 * @return CentralBooking\GUI\TabInteractiveComponent
 */
function git_tab_interactive_pane(array $args = [])
{
    $tab_pane = new CentralBooking\GUI\TabInteractiveComponent();
    foreach ($args as $pane) {
        $tab_pane->addPane($pane['label'], $pane['content']);
    }
    return $tab_pane;
}

/**
 * @param array{label:string,content:ComponentInterface|DisplayerInterface}[] $args
 * @return CentralBooking\GUI\TabStatefulComponent
 */
function git_tab_stateful_pane(array $args = [])
{
    $tab_pane = new CentralBooking\GUI\TabStatefulComponent();
    foreach ($args as $pane) {
        $tab_pane->addPane($pane['label'], $pane['content']);
    }
    return $tab_pane;
}

function git_text_component(array $args = [])
{
    $text = $args['text'] ?? '';
    $tag = $args['tag'] ?? 'p';
    $component = new CentralBooking\GUI\TextComponent($tag, $text);
    if (isset($args['id'])) {
        $component->id = $args['id'];
    }
    if (isset($args['class'])) {
        $classes = explode(' ', $args['class']);
        foreach ($classes as $class) {
            $component->class_list->add($class);
        }
    }
    unset($args['text'], $args['tag'], $args['id'], $args['class']);
    foreach ($args as $attr => $val) {
        $component->attributes->set($attr, $val);
    }
    return $component;
}