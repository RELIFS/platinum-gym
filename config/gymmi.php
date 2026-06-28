<?php

return [
    'knowledge_base_path' => env('GYMMI_KNOWLEDGE_BASE_PATH') ?: resource_path('data/gymmi/knowledge-base.json'),
    'knowledge_overrides_path' => env('GYMMI_KNOWLEDGE_OVERRIDES_PATH') ?: resource_path('data/gymmi/knowledge-overrides.json'),
    'knowledge_source_path' => env('GYMMI_KNOWLEDGE_SOURCE_PATH') ?: base_path('platinumgym-figma/docs/source-data/data_AI_Chatbot.xlsx'),
];
