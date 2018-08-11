<?php

return [

  ['GET', '/statistic', ['EventGetController', 'get_all']],
  ['POST', '/event', ['EventController', 'insert']],
  ['GET', '/test', ['EventUpdateController', 'test']],
];
