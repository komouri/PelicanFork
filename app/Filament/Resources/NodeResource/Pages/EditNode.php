<?php

namespace App\Filament\Resources\NodeResource\Pages;

use App\Filament\Resources\NodeResource;
use App\Models\Allocation;
use App\Models\Node;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\HtmlString;
use Webbingbrasil\FilamentCopyActions\Forms\Actions\CopyAction;

class EditNode extends EditRecord
{
    protected static string $resource = NodeResource::class;

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Tabs::make('Tabs')
                ->columns([
                    'default' => 2,
                    'sm' => 3,
                    'md' => 3,
                    'lg' => 4,
                ])
                ->persistTabInQueryString()
                ->columnSpanFull()
                ->tabs([
                    Tabs\Tab::make('Basic Settings')
                        ->icon('tabler-server')
                        ->schema((new CreateNode())->form($form)->getComponents()),
                    Tabs\Tab::make('Advanced Settings')
                        ->icon('tabler-server-cog'),
                    Tabs\Tab::make('Configuration')
                        ->icon('tabler-code')
                        ->schema([
                            Forms\Components\Placeholder::make('instructions')
                                ->columnSpanFull()
                                ->content(new HtmlString('
                                  Save this file to your <span title="usually /etc/pelican/">daemon\'s root directory</span>, named <code>config.yml</code>
                            ')),
                            Forms\Components\Textarea::make('config')
                                ->label('/etc/pelican/config.yml')
                                ->disabled()
                                ->rows(19)
                                ->hintAction(CopyAction::make())
                                ->columnSpanFull(),
                        ]),
                    Tabs\Tab::make('Allocations')
                        ->icon('tabler-plug-connected')
                        ->columns([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 4,
                        ])
                        ->schema([
                            Forms\Components\Section::make('Create Allocation')
                                ->columnSpan(4)
                                ->columns([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 4,
                                    'lg' => 5,
                                ])
                                //->inlineLabel()
                                ->headerActions([
                                    Forms\Components\Actions\Action::make('submit')
                                        ->color('success')
                                        ->action(function () {
                                            // ...
                                        }),
                                ])
                                ->schema([
                                    Forms\Components\TextInput::make('ip')
                                        ->columnSpan([
                                            'default' => 1,
                                            'sm' => 1,
                                            'md' => 2,
                                            'lg' => 2,
                                        ])
                                        ->label('IP Address')
                                        ->placeholder('x.x.x.x')
                                        ->helperText('IP address to assign ports to'),
                                    Forms\Components\TagsInput::make('port')
                                        ->columnSpan([
                                            'default' => 1,
                                            'sm' => 1,
                                            'md' => 1,
                                            'lg' => 1,
                                        ])
                                        ->placeholder('25565')
                                        ->helperText('Individual ports or port ranges here separated by spaces')
                                        ->splitKeys(['Tab', ' ']),
                                    Forms\Components\TextInput::make('ip_alias')
                                        ->columnSpan([
                                            'default' => 1,
                                            'sm' => 2,
                                            'md' => 1,
                                            'lg' => 2,
                                        ])
                                        ->label('Alias')
                                        ->placeholder('minecraft.pelican.dev')
                                        ->helperText('Display name to help you remember.'),
                                ]),
                            Forms\Components\Repeater::make('allocations')
                                ->orderColumn('server_id')
                                ->columnSpan(4)
                                ->columns([
                                    'default' => 1,
                                    'sm' => 3,
                                    'md' => 4,
                                    'lg' => 9,
                                ])
                                ->relationship()
                                ->addActionLabel('Create New Allocation')
                                ->addAction(fn ($action) => $action->color('info'))
                                ->schema([
                                    Forms\Components\TextInput::make('ip')
                                        ->label('IP Address')
                                        ->placeholder('x.x.x.x')
                                        ->columnSpan([
                                            'default' => 1,
                                            'sm' => 2,
                                            'md' => 3,
                                            'lg' => 2,
                                        ]),
                                    Forms\Components\TextInput::make('port')
                                        ->placeholder('25565')
                                        ->columnSpan([
                                            'default' => 1,
                                            'sm' => 1,
                                            'md' => 1,
                                            'lg' => 1,
                                        ])
                                        ->minValue(0)
                                        ->maxValue(65535)
                                        ->numeric(),
                                    Forms\Components\TextInput::make('ip_alias')
                                        ->placeholder('mincraft.pelican.dev')
                                        ->columnSpan([
                                            'default' => 1,
                                            'sm' => 2,
                                            'md' => 2,
                                            'lg' => 3,
                                        ])
                                        ->label('Alias'),
                                    Forms\Components\TextInput::make('server')
                                        ->columnSpan([
                                            'default' => 1,
                                            'sm' => 1,
                                            'md' => 2,
                                            'lg' => 3,
                                        ])
                                        ->formatStateUsing(fn (Allocation $allocation) => $allocation->server?->name)
                                        ->activeUrl(true)
                                        ->placeholder('Not assigned'),
                                ]),
                        ]),

                ]),
        ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $node = Node::findOrFail($data['id']);

        $data['config'] = $node->getYamlConfiguration();

        return $data;
    }

    protected function getSteps(): array
    {
        return [
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
