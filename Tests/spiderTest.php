<?php

use PHPUnit\Framework\TestCase;

class spiderTest extends TestCase {
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

    public function testSpiderShouldMoveExactly3Tiles() {
        // Arrange: Set up the initial state of the board
        $this->model->board = ['0,0' => 'Q', '0,1' => 'Q', '-1,0' => 'A', '-1,2' => 'B', '-1,-1' => 'S', '-2,2' => 'B'];

        $this->controllerMock->expects($this->once())
                             ->method('moveTile')
                             ->with('-1,-1', '-1,-1'); // Expecting spider to move from -1,-1 to -1,-1

        // Act: Perform the grasshopper movement
        $this->controllerMock->moveTile('-1,-1', '-1,-1');

        // Assertion: Verify that the movement was successful and there are no errors
        $this->assertFalse($this->controllerMock->hasError());
        $this->assertNull($this->controllerMock->getError());
    }
    
    public function testSpiderCanNotMoveLessThen3Tiles() {
        // Arrange: Set up the initial state of the board
        $this->model->board = ['0,0' => 'Q', '0,1' => 'Q', '-1,0' => 'A', '-1,2' => 'B', '-1,-1' => 'S', '-2,2' => 'B'];

        $this->controllerMock->expects($this->once())
                             ->method('moveTile')
                             ->with('-1,-1', '-2,0'); // Expecting spider to move from -1,-1 to -2,0

        // Act: Perform the grasshopper movement
        $this->controllerMock->moveTile('-1,-1', '-2,0');

        // Assertion: Verify that the movement was not successful and there are errors
        $this->assertTrue($this->controllerMock->hasError());
        $this->assertNotNull($this->controllerMock->getError());
    }

    public function testSpiderCanNotMoveMoreThen3Tiles() {
        // Arrange: Set up the initial state of the board
        $this->model->board = ['0,0' => 'Q', '0,1' => 'Q', '-1,0' => 'A', '-1,2' => 'B', '-1,-1' => 'S', '-2,2' => 'B'];

        $this->controllerMock->expects($this->once())
                             ->method('moveTile')
                             ->with('-1,-1', '-3,2'); // Expecting spider to move from -1,-1 to -3,2

        // Act: Perform the grasshopper movement
        $this->controllerMock->moveTile('-1,-1', '-3,2');

        // Assertion: This move should not be successful and should return errors
        $this->assertTrue($this->controllerMock->hasError());
        $this->assertNotNull($this->controllerMock->getError());
    }

    public function testSpiderCanNotMoveOverOccupiedTiles(){
        // Arrange: Set up the initial state of the board (non-empty board)
        $this->model->board = ['0,0' => 'Q', '0,1' => 'Q', '-1,0' => 'A', '-1,2' => 'B', '-1,-1' => 'S', '-2,2' => 'B'];

        $this->controllerMock->expects($this->once())
                             ->method('moveTile')
                             ->with('-1,-1', '0,0'); // Expecting soldierAnt to move from -1,-1 to 0,0

        // Act: Perform the grasshopper movement
        $this->controllerMock->moveTile('-1,-1', '0,0');

        // Assertion: This move should not be successful and should return errors
        $this->assertTrue($this->controllerMock->hasError());
        $this->assertNotNull($this->controllerMock->getError());
    }


}
