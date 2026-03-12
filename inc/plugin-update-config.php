<?php
/**
 * Автоматическое обновление плагина с GitHub
 */

// Путь к самой библиотеке PUC относительно текущего файла
$puc_path = plugin_dir_path(__FILE__) . 'plugin-update-checker/plugin-update-checker.php';

if (file_exists($puc_path)) {
    require_once $puc_path;
} else {
    return; // Если библиотека не найдена, выходим
}

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

// Инициализация. 
// ВАЖНО: Для плагина во втором параметре передаем путь к ГЛАВНОМУ файлу плагина.
$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/NellFotina/jarco-engine',
    dirname(__DIR__) . '/jarco-engine.php', // Поднимаемся на уровень выше к главному файлу
    'jarco-engine'
);

// Авторизация
$myUpdateChecker->setAuthentication('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');
$myUpdateChecker->setBranch('main');