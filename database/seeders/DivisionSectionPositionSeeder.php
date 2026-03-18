<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Position;
use App\Models\Section;
use Illuminate\Database\Seeder;

class DivisionSectionPositionSeeder extends Seeder
{
    /**
     * Seed divisions, sections, and position titles from the provided structure.
     */
    public function run(): void
    {
        $catalog = [
            [
                'division_name' => 'National Seed Quality Control Services Division (NSQCS)',
                'sections' => [
                    [
                        'section_name' => 'Seed Testing Section (Region X / CDO Unit)',
                        'positions' => [
                            'Seed Analyst',
                            'Laboratory Technician',
                        ],
                    ],
                    [
                        'section_name' => 'Seed Certification Section',
                        'positions' => [
                            'Agriculturist II',
                            'Certification Officer',
                        ],
                    ],
                    [
                        'section_name' => 'Plant Material Certification Section',
                        'positions' => [
                            'Plant Inspector',
                            'Technical Staff',
                        ],
                    ],
                ],
            ],
            [
                'division_name' => 'National Plant Quarantine Services Division (NPQSD)',
                'sections' => [
                    [
                        'section_name' => 'Plant Quarantine Inspection Section',
                        'positions' => [
                            'Plant Quarantine Officer',
                            'Agriculturist II',
                        ],
                    ],
                    [
                        'section_name' => 'Pest Surveillance Section',
                        'positions' => [
                            'Field Inspector',
                            'Technical Staff',
                        ],
                    ],
                    [
                        'section_name' => 'Quarantine Regulation & Enforcement Section',
                        'positions' => [
                            'Regulatory Officer',
                            'Enforcement Staff',
                        ],
                    ],
                ],
            ],
            [
                'division_name' => 'Plant Product Safety Services Division (PPSSD)',
                'sections' => [
                    [
                        'section_name' => 'Pesticide Analytical Laboratory Section (SPAL - CDO)',
                        'positions' => [
                            'Chemist / Analyst',
                            'Laboratory Technician',
                        ],
                    ],
                    [
                        'section_name' => 'Contaminants Laboratory Section',
                        'positions' => [
                            'Laboratory Analyst',
                            'Technical Staff',
                        ],
                    ],
                ],
            ],
            [
                'division_name' => 'Crop Research and Production Support Division',
                'sections' => [
                    [
                        'section_name' => 'Crop Research Section',
                        'positions' => [
                            'Researcher',
                            'Agriculturist',
                        ],
                    ],
                    [
                        'section_name' => 'Production Support Section',
                        'positions' => [
                            'Field Technician',
                            'Technical Staff',
                        ],
                    ],
                ],
            ],
            [
                'division_name' => 'Administrative & Finance Division',
                'sections' => [
                    [
                        'section_name' => 'Accounting Section',
                        'positions' => [
                            'Accountant',
                            'Accounting Staff',
                        ],
                    ],
                    [
                        'section_name' => 'Budget Section',
                        'positions' => [
                            'Budget Officer',
                        ],
                    ],
                    [
                        'section_name' => 'Cashier Section',
                        'positions' => [
                            'Cashier',
                        ],
                    ],
                    [
                        'section_name' => 'Procurement Section',
                        'positions' => [
                            'Procurement Officer',
                        ],
                    ],
                    [
                        'section_name' => 'Human Resource Section',
                        'positions' => [
                            'HR Officer',
                        ],
                    ],
                    [
                        'section_name' => 'Internal Audit Unit',
                        'positions' => [
                            'Internal Auditor',
                        ],
                    ],
                ],
            ],
        ];

        $nextDivisionId = ((int) Division::query()->max('division_id')) + 1;
        $nextSectionId = ((int) Section::query()->max('section_id')) + 1;
        $nextPositionId = ((int) Position::query()->max('position_id')) + 1;

        foreach ($catalog as $divisionData) {
            $division = Division::query()->where('division_name', $divisionData['division_name'])->first();

            if (!$division) {
                $division = Division::query()->create([
                    'division_id' => $nextDivisionId++,
                    'division_name' => $divisionData['division_name'],
                    'division_code' => null,
                    'description' => null,
                ]);
            }

            foreach ($divisionData['sections'] as $sectionData) {
                $section = Section::query()
                    ->where('section_name', $sectionData['section_name'])
                    ->first();

                if (!$section) {
                    Section::query()->create([
                        'section_id' => $nextSectionId++,
                        'section_name' => $sectionData['section_name'],
                        'section_code' => null,
                        'division_id' => $division->division_id,
                        'description' => null,
                    ]);
                } elseif ((int) $section->division_id !== (int) $division->division_id) {
                    $section->update([
                        'division_id' => $division->division_id,
                    ]);
                }

                foreach ($sectionData['positions'] as $positionTitle) {
                    $positionExists = Position::query()->where('position_title', $positionTitle)->exists();

                    if ($positionExists) {
                        continue;
                    }

                    Position::query()->create([
                        'position_id' => $nextPositionId++,
                        'position_title' => $positionTitle,
                    ]);
                }
            }
        }
    }
}
