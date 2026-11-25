<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger. All Rights Reserved.
 * Contributor(s): Valmir C. Trindade - Brazilian Portuguese Translation - www.ttcasolucoes.com.br
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

$languageStrings = [
    'LBL_SUMMARY_FILTER' => 'Filtro',
    'LBL_SUMMARY_CLEAR_FILTERS' => 'Limpar filtros',
    'LBL_SUMMARY_DATE_FROM' => 'Data de',
    'LBL_SUMMARY_DATE_TO' => 'Data até',
    'LBL_SUMMARY_ASSIGNEE' => 'Responsável',
    'LBL_SUMMARY_TASK_COUNT' => '%s tarefas',
    'LBL_SUMMARY_ACTIVE_COUNT' => '%s ativas',
    'LBL_SUMMARY_OVERDUE_COUNT' => '%s atrasadas',
    'LBL_SUMMARY_PROJECT_PROGRESS' => 'Progresso do projeto',
    'LBL_SUMMARY_DATE_RANGE' => 'De – Até',
    'LBL_SUMMARY_NO_DATES' => 'Sem datas',
    'LBL_SUMMARY_NO_MILESTONE' => 'Sem marco',
    'LBL_SUMMARY_UNASSIGNED' => 'Não atribuído',
    'LBL_SUMMARY_DAYS_OVERDUE' => '%s dias de atraso',
    'LBL_SUMMARY_NO_STATUS' => 'Sem status',
    'LBL_CHART_RANGE_REMEDY' => 'Mova as datas para o período do projeto ou corrija as datas do projeto.',
    'LBL_CHART_DEPENDENCY_REMEDY' => 'Mova o início para depois do fim do registro anterior ou corrija ou remova a dependência incorreta.',
    'LBL_CHART_MILESTONE_REMEDY' => 'Conclua a tarefa indicada até a data do marco ou corrija a data do marco.',
    'LBL_CHART_OVERDUE_REMEDY' => 'Atualize as datas ou marque a tarefa como concluída se o trabalho terminou.',
    'LBL_CHART_END' => 'Fim',
    'LBL_CHART_START' => 'Início',
    'LBL_CHART_DAY_COUNT' => 'Dias',
    'LBL_MILESTONE_RANGE_HELP' => 'O marco abrange desde o início mais cedo das tarefas vinculadas até o prazo salvo no registro. As tarefas devem terminar até esse dia. Sem data de início, apenas o marcador do prazo é exibido. As datas não são alteradas automaticamente. Sem data própria, o marco usa o fim previsto do projeto.',
    'LBL_TASK_OUTSIDE_PROJECT_DATES' => 'As datas da tarefa devem estar entre o início e o fim planejado do projeto.',
    'LBL_TASK_MILESTONE_PROJECT' => 'O marco selecionado deve pertencer ao projeto da tarefa.',
    'LBL_MILESTONE_BEFORE_TASK_END' => 'O marco não pode terminar antes das datas das tarefas vinculadas.',
    'LBL_TASK_AFTER_MILESTONE' => 'As datas da tarefa não podem ultrapassar o prazo do marco vinculado.',
    'LBL_CHART_TABLE' => 'Tabela de tarefas',
    'LBL_CLOSED_TASK_STATUSES' => 'Status de tarefas encerradas',
    'LBL_CLOSED_TASK_STATUSES_HELP' => 'Selecione os status que não devem ser marcados como atrasados. O percentual de progresso permanecerá inalterado.',
    'LBL_CHART_OVERDUE' => 'Atrasada',
    'LBL_CHART_PERIOD' => 'Período',
    'LBL_CHART_SETTINGS_SETUP' => 'As configurações do gráfico estarão disponíveis após atualizar o banco de dados do sistema.',
    'LBL_OUTSIDE_PROJECT_RANGE' => 'As datas da tarefa ultrapassam o período planejado do projeto.',
    'LBL_DEPENDENCY_VIOLATION' => 'A tarefa ou o marco começa antes de o predecessor terminar.',
    'LBL_DEPENDENCY_SELF' => 'Um registro não pode ser vinculado a si mesmo.',
    'LBL_DEPENDENCY_DUPLICATE' => 'Esta dependência já existe.',
    'LBL_DEPENDENCY_CYCLE' => 'Esta dependência criaria um ciclo.',
    'LBL_DEPENDENCY_HELP' => 'Conecte tarefas entre si ou marcos entre si com Fim → Início. Clique duas vezes para remover o vínculo. As datas não são alteradas automaticamente.',
    'LBL_DEPENDENCY_SETUP' => 'As dependências estarão disponíveis após atualizar o banco de dados do sistema.',
    'LBL_UNSCHEDULED_TASKS' => 'Não agendadas',
    'LBL_MISSING_START_DATE' => 'Data de início ausente',
    'LBL_MISSING_END_DATE' => 'Data de término ausente',
    'LBL_INVALID_DATE_RANGE' => 'Intervalo de datas inválido',
    'LBL_EMPTY_PROJECT_CHART' => 'As tarefas aparecem na linha do tempo quando têm datas válidas de início e término. Adicione uma tarefa para começar a planejar o projeto.',
    'Milestones list' => 'Lista Milestones',
    'Add Project Task' => 'Adicionar tarefa do projeto',
    'Project Milestone Name' => 'Nome do Milestone',
    'Milestone Date' => 'Data Milestone',
    'Project Task Name' => 'Nome Tarefa Projeto',
    'Is Converted From Opportunity' => 'Convertido de uma oportunidade',
    '10%' => '10%',
    '20%' => '20%',
    '30%' => '30%',
    '40%' => '40%',
    '50%' => '50%',
    '60%' => '60%',
    '70%' => '70%',
    '80%' => '80%',
    '90%' => '90%',
    '100%' => '100%',
    'LBL_DAY_SCALE' => 'Dia',
    'LBL_WEEK_SCALE' => 'Semana',
    'LBL_MONTH_SCALE' => 'Mês',
    'LBL_YEAR_SCALE' => 'Ano',
    'CV_ALL' => 'Todos os projetos',
    // Basic Strings
    'Project'                              => 'Projetos',
    'SINGLE_Project'                       => 'Projeto',
    'LBL_ADD_RECORD'                       => 'Adicionar Projeto',
    'LBL_RECORDS_LIST'                     => 'Lista Projetos',

    // Blocks
    'LBL_PROJECT_INFORMATION'              => 'Detalhes Projeto',

    //Field Labels
    'Project Name'                         => 'Nome Projeto',
    'Start Date'                           => 'Data Início',
    'Target End Date'                      => 'Data Final Alvo',
    'Actual End Date'                      => 'Data Final Atual',
    'Project No'                           => 'No. Projeto',
    'Target Budget'                        => 'Orçamento Alvo',
    'Project Url'                          => 'URL do Projeto',
    'Progress'                             => 'Progresso',
    'Type'                                 => 'Tipo',
    'Related to'                           => 'Relacionado a',

    //Summary Information
    'LBL_TASKS_OPEN'                       => 'Tarefas Abertas',
    'LBL_TASKS_DUE'                        => 'Tarefas Vencidas',
    'LBL_TASKS_COMPLETED'                  => 'Tarefas Concluídas',
    'LBL_PEOPLE'                           => 'Pessoas',
    'LBL_TASKS_HIGH'                       => 'Prioridade Alta',
    'LBL_TASKS_NORMAL'                     => 'Prioridade Normal',
    'LBL_TASKS_LOW'                        => 'Prioridade Baixa',
    'LBL_TASKS_OTHER'                      => 'Outra Prioridade',
    'LBL_SELECT_PROGRESS'                  => 'Progresso - Todos',

    //Related List
    'LBL_CHARTS'                           => 'Gráfico',
    'LBL_TASKS_LIST'                       => 'Lista Tarefas',
    'LBL_MILESTONES'                       => 'Milestones',
    'LBL_TASKS'                            => 'Tarefas',
    'LBL_STATUS_IS'                        => 'Status é',
    'LBL_STATUS'                           => 'Status',
    'LBL_TICKET_PRIORITY'                  => 'Prioridade',
    'LBL_MILESTONES_LIST'                  => 'Lista Milestones',

    //Summary View Widgets
    'LBL_DOWNLOAD_FILE'                    => 'Arquivo Download',

    //picklist values
    'prospecting'                          => 'Prospectando',
    'in progress'                          => 'Em Andamento',
    'initiated'                            => 'Iniciado',
    'waiting for feedback'                 => 'Aguardando por Feedback',
    'on hold'                              => 'Aguardando',
    'archived'                             => 'Arquivado',
    'completed'                            => 'Concluído',
    'delivered'                            => 'Entregue',
    'administrative'                       => 'Administrativo',
    'operative'                            => 'Operacional',
    'other'                                => 'Outro',
    'low'                                  => 'Baixo',
    'normal'                               => 'Normal',
    'high'                                 => 'Alto',

    //Gantt chart
    'LBL_CHART'                            => 'Gráfico',
    'LBL_PROGRESS_CHART'                   => 'Andamento Gráfico',
    'LBL_TASK_NAME'                        => 'Nome Tarefa',
    'LBL_START_DATE'                       => 'Data Início',
    'LBL_END_DATE'                         => 'Data Final',
    'LBL_DURATION'                         => 'Duração',
    'LBL_INFO'                             => 'Info',
    'LBL_GANTT_INFO1'                      => 'Deslize para o lado direito da Tarefa para expandir a duração',
    'LBL_GANTT_INFO2'                      => 'Arraste para o lado esquerdo ou para o centro da Tarefa para alterar a data de início e término.',
    'LBL_EDIT_PROJECT_TASK_STATUS_COLOR'   => 'Editar a côr do Status da Tarefa do Projeto',
    'LBL_SELECT_PROJECT_TASK_STATUS_COLOR' => 'Selecionar a côr do Status da Tarefa do Projeto',
    'LBL_SELECT_STATUS'                    => 'Selecionar Status',
    'LBL_EDIT_COLOR'                       => 'Editar Côr',
];

$jsLanguageStrings = [
    'JS_LBL_SELECT_STATUS' => 'Selecionar Status',
    'JS_LBL_SELECT_PROGRESS' => 'Progresso - Todos',
    'JS_START_DATE' => 'Data Início',
    'JS_END_DATE' => 'Data Final',
    'JS_PROJECT_TASKS' => 'Tarefas',
    'JS_TASK_NAME' => 'Nome Tarefa',
    'JS_COLOR_SAVED_SUCESSFULLY' => 'A côr foi salva com sucesso',
];