<?php

return [
  ['GET', '/statistic/{data_type}', ['EventController', 'get_all']],
  ['GET', '/statistic', ['EventController', 'get_all']],
  ['POST', '/event', ['EventController', 'insert']],
  ['GET', '/test', ['EventController', 'test']],
];
