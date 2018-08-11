<?php

return [
  
  ['GET', '/statistic', ['EventController', 'get_all']],
  ['POST', '/event', ['EventController', 'insert']],
  ['GET', '/test', ['EventController', 'test']],
];
