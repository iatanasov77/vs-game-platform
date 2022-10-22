import React from 'react';

const GameStatistics = () => {

    let gameResults = [];
    for ( let i = 0; i <= 5; i++ ) {
        let rowObject   = (
            <tr key={i}>
                <td className="border-end border-bottom p-2"></td>
                <td className="border-start border-bottom p-2"></td>
            </tr>
        );
        gameResults.push( rowObject );
    }
    
    return (
        <div style={{ position: "relative", top: "130px" }}>
            <table>
                <thead>
                    <tr>
                        <th className="border-end border-bottom p-2 font-weight-bold">WE</th>
                        <th className="border-start border-bottom p-2 font-weight-bold">YOU</th>
                    </tr>
                </thead>
                <tbody>
                    { gameResults }
                </tbody>
            </table>
        </div>
    );
}

export default GameStatistics;
