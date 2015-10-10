<?php
require_once 'bootstrap.php';

$injector->make('Kastilyo\RabbitHole\Spec\Subscriber')->consume();
