<?php
global $db;
echo "
    <div class=\"container content\">
            <div class=\"row page_title\">
                <h4>" . _FAQ_DETAIL . "</h4>
            </div>

		<div class=\"row\">
            <div class=\"col-xs-10\">
                <div class=\"container\">";
                    $result_questions = $db -> sql_query("SELECT*FROM faq WHERE is_deleted = '0'", "", "array");
                    if (sizeof($result_questions) > 0 AND $result_questions[0] != ''){
                    echo "
                        <div id=\"accordion\" class=\"accordion panel-group\">";
                        foreach ($result_questions as $item) {
                            $id = $item['id'];
                            $question = $item['question'];
                            $answer = $item['answer'];
                            echo "
                                <div class=\"panel panel-default\">
                                    <div class=\"panel-heading\">
                                        <h4 class=\"panel-title\">
                                            <a data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapse$id\" class=\"collapsed\">
                                                <i class=\"fa fa-question-circle pr-10\"></i> $question
                                            </a>
                                        </h4>
                                    </div>
                                    <div id=\"collapse$id\" class=\"panel-collapse collapse\">
                                        <div class=\"panel-body\"> $answer </div>
                                    </div>
                                </div>
                            ";
                        }
                    echo "
                        </div>		<!--------END accordion-------->";
                    }
                echo "
				</div>
            </div>			<!-----END col-xs-10--->
        </div>
    </div>
	";
?>