<?php

$modx->getService('evocaptcha','evoCaptcha',MODX_CORE_PATH.'components/evocaptcha/model/evocaptcha/');
if($hook){
    if($hook->type=='pre'){
        $properties = $hook->modx->fromJSON($formit->config['evocaptcha']);
        if(empty($properties))$properties=array();
        if(!empty($formit->config['submitVar'])){$properties['sessionPrefix'] = $formit->config['submitVar'];}
        $modx->evocaptcha->initialize($properties);
        $modx->evocaptcha->setWord();
        $hook->setValue('evocaptcha',$modx->evocaptcha->getConnector());
        return true;
    }
}

if($validator){
    $properties = $validator->modx->fromJSON($validator->formit->config['evocaptcha']);
    if(empty($properties))$properties=array();
    if(!empty($validator->formit->config['submitVar'])){$properties['sessionPrefix'] = $validator->formit->config['submitVar'];}
    $modx->evocaptcha->initialize($properties);
    $validator->fields['evocaptcha'] = $modx->evocaptcha->getConnector();
    if(!$modx->evocaptcha->checkWord($value))$validator->addError($key,$modx->lexicon('evocaptcha.errors.invalid_code'));
    return true;
}