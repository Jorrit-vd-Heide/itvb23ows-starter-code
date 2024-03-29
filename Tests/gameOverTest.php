<?php

use PHPUnit\Framework\TestCase;

class gameOverTest extends TestCase {
    protected $model;
    protected $controllerMock;

    protected function setUp(): void {
        parent::setUp();
        // Arrange: Set up any necessary fixtures or dependencies
        $GLOBALS['OFFSETS'] = [[0, 1], [1, 0], [1, 1], [-1, 0], [0, -1], [-1, -1]];

        // Mock the model and view
        $this->model = $this->getMockBuilder(HiveGameModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $viewMock = $this->createMock(HiveGameView::class);

        $this->controllerMock = $this->getMockBuilder(HiveGameController::class)
            ->setConstructorArgs([$modelMock, $viewMock])
            ->onlyMethods(['moveTile', 'playTile']) // Mock only the pass method
            ->getMock();
    }

    // QueenBee surrounded
    public function testGameIsOver() {
        // Arrange: Set up the initial state of the board
        $this->model->board = ['0,0' => 'Q', '0,1' => 'Q', '-1,0' => 'B', '-1,2' => 'B', '0,-1' => 'A', '-2,2' => 'B', '1,-1' => 'A'];

        // Act: Perform the move and play
        $this->controllerMock->expects($this->once())
                             ->method('moveTile')
                             ->with('1,-1', '1,0'); // Expecting soldierAnt to move from 1,-1 to 1,0

        $this->controllerMock->expects($this->once())
                             ->method('playTile')
                             ->with('A', '1,-1')
                             ->willReturn('A', '1,-1');

        // Assertion: Pass should not be successful and should return errors
        $this->assertTrue($controllerMock->determineWinner());
    }
}
