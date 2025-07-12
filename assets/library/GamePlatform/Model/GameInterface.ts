interface IGame
{
    id: number;
    slug: string;
    title: string;
    url: string;
    
    room?: any;
    deck?: any;
}

export default IGame;