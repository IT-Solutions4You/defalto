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
    'LBL_PROGRESS_HOURS_PER_DAY_HELP' => 'Usado quando a tarefa não tem horas planejadas.',
    'LBL_PROGRESS_FALLBACK_HOURS_HELP' => 'Quantas horas uma tarefa sem estimativa e datas completas deve representar no cálculo.',
    'LBL_PROGRESS_COMPLETED_STATUSES_HELP' => 'Tarefas nesses status são consideradas totalmente concluídas.',
    'LBL_PROGRESS_EXCLUDED_STATUSES_HELP' => 'Tarefas nesses status não são incluídas no progresso do projeto.',

    'LBL_PROJECT_PROGRESS_CALCULATION_TITLE' => 'Como o progresso do projeto é calculado',
    'LBL_PROJECT_PROGRESS_CALCULATION' => '**Tarefas maiores** têm mais influência no progresso do projeto.

O cálculo usa primeiro as **horas planejadas**. Se não houver, usa as datas da tarefa e as horas por dia corrido. Sem datas completas, usa as **horas alternativas**.

**Tarefas concluídas** contam como finalizadas. As **excluídas** não contam. As demais usam o progresso informado.

O progresso é atualizado quando as tarefas mudam. Você pode editá-lo manualmente, mas a **próxima alteração de uma tarefa o recalculará**.

Para parar as atualizações automáticas, **desative esta ação ou o processo**.',
    'LBL_PROJECT_PROGRESS_TASK' => 'Calcular progresso do projeto',
    'LBL_PROGRESS_HOURS_PER_DAY' => 'Horas por dia corrido',
    'LBL_PROGRESS_FALLBACK_HOURS' => 'Horas para tarefas sem estimativa e datas',
    'LBL_PROGRESS_COMPLETED_STATUSES' => 'Status concluídos (100%)',
    'LBL_PROGRESS_EXCLUDED_STATUSES' => 'Status excluídos',

    'LBL_TASK_ASSIGNEE' => 'Responsável',
    'CV_ALL' => 'Todas as tarefas do projeto',
    // Basic Strings
    'SINGLE_ProjectTask'           => 'Tarefa Projeto',
    'LBL_ADD_RECORD'               => 'Adicionar Tarefa Projeto',
    'LBL_RECORDS_LIST'             => 'Lista Tarefa Projeto',

    // Blocks
    'LBL_PROJECT_TASK_INFORMATION' => 'Detalhes Tarefa Projeto',
    'LBL_PROJECTS_LIST'            => 'Lista Projetos',
    'LBL_TASKS_LIST'               => 'Lista Tarefas',
    'LBL_MILESTONES_LIST'          => 'Lista Milestones',

    //Field Labels
    'Project Task Name'            => 'Nome Tarefa Projeto',
    'Project Task No'              => 'No. Tarefa Projeto',
    'Project Task Number'          => 'Número Tarefa Projeto',
    'Status'                       => 'Status',
    'Priority'                     => 'Prioridade',
    'Progress'                     => 'Progresso',
    'Type'                         => 'Tipo',
    'Worked Hours'                 => 'Horas Trabalhadas',
    'Start Date'                   => 'Data Início',
    'End Date'                     => 'Data Final',
    'Related to'                   => 'Projeto relacionado', //Relacionado a
    'administrative'               => 'Administrativo',
    'operative'                    => 'Operacional',
    'other'                        => 'Outro',
    'low'                          => 'Baixo',
    'normal'                       => 'Normal',
    'high'                         => 'Alto',
    'Created Time'                 => 'Hora Criação',
    'Modified Time'                => 'Hora Modificação',
    'description'                  => 'Descrição',
    'Assigned To'                  => 'Designado a',
    'Open'                         => 'Aberto',
    'In Progress'                  => 'Em Andamento',
    'Completed'                    => 'Concluído',
    'Deferred'                     => 'Adiado',
    'Canceled'                     => 'Cancelado',
    'LBL_NO_DATE_VALUE_MSG'        => 'ou a Tarefa do Projeto não tem Data Início e/ou Data Fim',
];