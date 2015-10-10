<?php
require_once 'bootstrap.php';

$subscriber = $injector->make('Kastilyo\RabbitHole\Spec\Subscriber');

$subscriber->consume();
