<?php
global $db;
echo "
    <div class=\"section content\">
        <div class=\"container\">
            <div class=\"row\">
                <div class=\"col-xs-10\">
                    <h4 class=\"page_title\">" . _FAQ_DETAIL . "</h4>
                </div>
            </div>

            <div class=\"row\">
                <div class=\"col-xs-10\">
                    <div id=\"accordion\" class=\"accordion panel-group\">
                        <h6>Основное</h6>

                        <div class=\"panel panel-default\">
                            <div class=\"panel-heading\">
                                <h4 class=\"panel-title\">
                                    <a data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapse01\" class=\"collapsed\">Что такое &laquo;PROJECT_NAME&raquo;?</a>
                                 </h4>
                            </div>
                            <div id=\"collapse01\" class=\"panel-collapse collapse\">
                                <div class=\"panel-body\"> &laquo;PROJECT_NAME&raquo; - проект, в котором каждый участник может транслировать свое видео с помощью мобильного устройства, планшета, веб-камеры.</div>
                            </div>
                        </div>

                        <div class=\"panel panel-default\">
                            <div class=\"panel-heading\">
                                <h4 class=\"panel-title\">
                                    <a data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapse02\" class=\"collapsed\">Что такое трансляция?</a>
                                </h4>
                            </div>
                            <div id=\"collapse02\" class=\"panel-collapse collapse\">
                                <div class=\"panel-body\"></div>
                            </div>
                        </div>

                            <div class=\"panel panel-default\">
                                <div class=\"panel-heading\">
                                    <h4 class=\"panel-title\">
                                        <a data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapse03\" class=\"collapsed\">
                                            Как создать свою трансляцию?
                                        </a>
                                    </h4>
                                </div>
                                <div id=\"collapse03\" class=\"panel-collapse collapse\">
                                    <div class=\"panel-body\"></div>
                                </div>
                            </div>

                            <div class=\"panel panel-default\">
                                <div class=\"panel-heading\">
                                    <h4 class=\"panel-title\">
                                        <a data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapse04\" class=\"collapsed\">
                                            Как я могу найти других пользователей и подписаться на них?
                                        </a>
                                    </h4>
                                </div>
                                <div id=\"collapse04\" class=\"panel-collapse collapse\">
                                    <div class=\"panel-body\"></div>
                                </div>
                            </div>

                            <div class=\"panel panel-default\">
                                <div class=\"panel-heading\">
                                    <h4 class=\"panel-title\">
                                        <a data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapse05\" class=\"collapsed\">
                                            Что значит &laquo;подписаться&raquo; на другого пользователя?
                                        </a>
                                    </h4>
                                </div>
                                <div id=\"collapse05\" class=\"panel-collapse collapse\">
                                    <div class=\"panel-body\"></div>
                                </div>
                            </div>

                            <h6>Трансляция</h6>

                            <div class=\"panel panel-default\">
                                <div class=\"panel-heading\">
                                    <h4 class=\"panel-title\">
                                        <a data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapse06\" class=\"collapsed\">
                                            Кто может видеть мои трансляции?
                                        </a>
                                    </h4>
                                </div>
                                <div id=\"collapse06\" class=\"panel-collapse collapse\">
                                    <div class=\"panel-body\"></div>
                                </div>
                            </div>

                            <div class=\"panel panel-default\">
                                <div class=\"panel-heading\">
                                    <h4 class=\"panel-title\">
                                        <a data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapse07\" class=\"collapsed\">
                                            Как я могу поделиться своим местоположением?
                                        </a>
                                    </h4>
                                </div>
                                <div id=\"collapse07\" class=\"panel-collapse collapse\">
                                    <div class=\"panel-body\"></div>
                                </div>
                            </div>

                            <div class=\"panel panel-default\">
                                <div class=\"panel-heading\">
                                    <h4 class=\"panel-title\">
                                        <a data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapse08\" class=\"collapsed\">
                                            Как я могу сделать свою трансляцию приватной?
                                        </a>
                                    </h4>
                                </div>
                                <div id=\"collapse08\" class=\"panel-collapse collapse\">
                                    <div class=\"panel-body\"></div>
                                </div>
                            </div>

                            <h6>Управление аккаунтом</h6>

                            <div class=\"panel panel-default\">
                                <div class=\"panel-heading\">
                                    <h4 class=\"panel-title\">
                                        <a data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapse09\" class=\"collapsed\">
                                            Как я могу зарегистрироваться в проекте &laquo;PROJECT_NAME&raquo;?
                                        </a>
                                    </h4>
                                </div>
                                <div id=\"collapse09\" class=\"panel-collapse collapse\">
                                    <div class=\"panel-body\"></div>
                                </div>
                            </div>

                            <div class=\"panel panel-default\">
                                <div class=\"panel-heading\">
                                    <h4 class=\"panel-title\">
                                        <a data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapse10\" class=\"collapsed\">
                                            Как я могу изменить логин или имя?
                                        </a>
                                    </h4>
                                </div>
                                <div id=\"collapse10\" class=\"panel-collapse collapse\">
                                    <div class=\"panel-body\"></div>
                                </div>
                            </div>

                            <div class=\"panel panel-default\">
                                <div class=\"panel-heading\">
                                    <h4 class=\"panel-title\">
                                        <a data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapse11\" class=\"collapsed\">
                                            Как я могу редактировать свой профиль?
                                        </a>
                                    </h4>
                                </div>
                                <div id=\"collapse11\" class=\"panel-collapse collapse\">
                                    <div class=\"panel-body\"></div>
                                </div>
                            </div>

                            <div class=\"panel panel-default\">
                                <div class=\"panel-heading\">
                                    <h4 class=\"panel-title\">
                                        <a data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapse12\" class=\"collapsed\">
                                            Как я могу блокировать или разблокировать другого пользователя?
                                        </a>
                                    </h4>
                                </div>
                                <div id=\"collapse12\" class=\"panel-collapse collapse\">
                                    <div class=\"panel-body\"></div>
                                </div>
                            </div>

                            <h6>Просмотр трансляции</h6>

                            <div class=\"panel panel-default\">
                                <div class=\"panel-heading\">
                                    <h4 class=\"panel-title\">
                                        <a data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapse13\" class=\"collapsed\">
                                            Как я могу оставить сообщение в чате?
                                        </a>
                                    </h4>
                                </div>
                                <div id=\"collapse13\" class=\"panel-collapse collapse\">
                                    <div class=\"panel-body\"></div>
                                </div>
                            </div>

                        </div>		<!--------END accordion-------->
                </div>			<!-----END col-xs-10--->
            </div>

        </div>
    </div>";
?>