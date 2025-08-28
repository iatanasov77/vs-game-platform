export interface IThemes
{
    name: string;
    lightTileColor: string;
    darkTileColor: string;
    
}

export class DarkTheme implements IThemes
{
    name = 'dark';
    lightTileColor = '#666';
    darkTileColor = '#333';
}
