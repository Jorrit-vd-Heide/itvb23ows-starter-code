<?php

use PHPUnit\Framework\TestCase;

class soldierAntTest extends TestCase {
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
            ->onlyMethods(['moveTile']) // Mock only the moveTile method
            ->getMock();
    }

    public function testSoldierAntMovement() {
        // Arrange: Set up the initial state of the board
        $this->controllerMock->expects($this->once())
                             ->method('moveTile')
                             ->with('0,0', '1,0'); // Expecting soldierAnt to move from 0,0 to 1,0

        // Act: Perform the grasshopper movement
        $this->controllerMock->moveTile('0,0', '1,0');

        // Assertion: Verify that the movement was successful and there are no errors
        $this->assertFalse($this->controllerMock->hasError());
        $this->assertNull($this->controllerMock->getError());
    }
    
    public function testSoldierAntUnlimitedMoves() {
        // Arrange: Set up the initial state of the board
        $this->model->board = ['0,0' => 'Q', '0,1' => 'Q', '0,-1' => 'A', '0,2' => 'B'];

        $this->controllerMock->expects($this->once())
                             ->method('moveTile')
                             ->with('0,-1', '-1,0'); // Expecting soldierAnt to move from 0,-1 to -1,0

        // Act: Perform the grasshopper movement
        $this->controllerMock->moveTile('0,-1', '-1,0');

        // Assertion: Verify that the movement was successful and there are no errors
        $this->assertFalse($this->controllerMock->hasError());
        $this->assertNull($this->controllerMock->getError());
    }

    public function testSoldierAntCanOnlyMoveToEmptySpaces() {
        // Arrange: Set up the initial state of the board
        $this->model->board = ['0,0' => 'Q', '0,1' => 'Q', '0,-1' => 'A', '0,2' => 'B'];

        $this->controllerMock->expects($this->once())
                             ->method('moveTile')
                             ->with('0,-1', '-1,0'); // Expecting soldierAnt to move from 0,-1 to -1,0

        // Act: Perform the grasshopper movement
        $this->controllerMock->moveTile('0,-1', '-1,0');

        // Assertion: This move should be successful and should return errors
        $this->assertFalse($this->controllerMock->hasError());
        $this->assertNull($this->controllerMock->getError());
    }

    public function testSoldierAntCanNotMoveToOccupiedPlace(){
        // Arrange: Set up the initial state of the board (non-empty board)
        $this->model->board = ['0,0' => 'Q', '0,1' => 'Q', '0,-1' => 'A', '0,2' => 'B'];

        $this->controllerMock->expects($this->once())
                             ->method('moveTile')
                             ->with('0,-1', '0,2'); // Expecting soldierAnt to move from 0,-1 to 0,2

        // Act: Perform the grasshopper movement
        $this->controllerMock->moveTile('0,-1', '0,2');

        // Assertion: This move should not be successful and should return errors
        $this->assertTrue($this->controllerMock->hasError());
        $this->assertNotNull($this->controllerMock->getError());
    }
}
